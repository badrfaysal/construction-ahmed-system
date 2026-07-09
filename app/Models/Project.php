<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\SupplierDebt;

class Project extends Model
{
    protected $table = 'sy2_projects';

    protected $fillable = [
        'client_id', 'name', 'address', 'area', 'default_supervision_pct',
        'initial_contract_value',
        'start_date', 'deliver_date', 'delivered_date',
        'current_stage', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'     => 'date',
            'deliver_date'   => 'date',
            'delivered_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function bands(): HasMany
    {
        return $this->hasMany(ProjectBand::class, 'project_id')->orderBy('sort_order');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'project_id');
    }

    // النظام القديم للأقساط — متسايب للتوافق فقط (بيرجّع فاضي بعد الاستبدال).
    // الجداول والموديل لسه موجودين عشان أي كود قديم بيعمل with('installments')
    // ما يكسرش، لكن كل التحصيل الفعلي بقى عبر contracts().
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'project_id')->orderBy('sort_order');
    }

    // عقود التقسيط الخاصة بالمشروع (النظام الجديد) — عادةً عقد واحد لكل مشروع
    public function contracts(): HasMany
    {
        return $this->hasMany(InstallmentContract::class, 'project_id')->latest('id');
    }

    // العقد النشط الحالي للمشروع (آخر عقد اتعمل)
    public function contract(): ?InstallmentContract
    {
        return $this->contracts->first();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'project_id')->orderByDesc('date');
    }

    // تحصيلات مباشرة من العميل (من صفحة المستحقات) — مش مربوطة بعقد تقسيط.
    // كل واحدة حركة "in" في sy2_transactions (بتغذّي المحفظة عبر الـ observer).
    public function clientPayments(): HasMany
    {
        return $this->hasMany(Transaction::class, 'project_id')
            ->where('ref_type', 'client_payment')
            ->orderByDesc('date');
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class, 'project_id');
    }

    public function supplierDebts(): HasMany
    {
        return $this->hasMany(SupplierDebt::class, 'project_id');
    }

    // Total we still owe suppliers on this project
    public function totalPendingDebt(): float
    {
        return (float) $this->supplierDebts
            ->where('status', '!=', 'paid')
            ->sum(fn ($d) => $d->remaining());
    }

    // Default supervision % for this project's bands/materials/workers —
    // falls back to the global Settings default when not set (zero)
    public function defaultSupervisionPct(): float
    {
        $own = (float) $this->default_supervision_pct;
        return $own > 0 ? $own : (float) Settings::current()->default_supervision_pct;
    }

    // Total agreed price = sum of all band client prices
    public function contractValue(): float
    {
        return (float) $this->bands->sum('client_price');
    }

    // Contract value locked in when the project was created from an approved
    // quote. Falls back to the computed contractValue() for projects created
    // before this column existed (or created directly, without a quote).
    public function initialContractValue(): float
    {
        return (float) ($this->initial_contract_value ?? $this->contractValue());
    }

    // Real amount billed to the client so far, based on the actual sell price
    // + supervision markup of every material/labor entry registered to date.
    // Includes general expenses (band_id null — e.g. petty/misc costs not tied
    // to one specific band) which live outside any band's own materials list.
    public function actualClientTotal(): float
    {
        return (float) $this->cached_actual_total;
    }

    public function computeActualClientTotal(): float
    {
        $bandsTotal = (float) $this->bands->sum(fn ($band) => $band->computeActualClientTotal());
        $generalMaterials = (float) $this->generalMaterials()->sum(fn ($m) => $m->netClientCost());
        return $bandsTotal + $generalMaterials;
    }

    // Materials/expenses registered directly on the project without a band
    // (e.g. a petty expense not tied to a specific phase of work)
    public function generalMaterials()
    {
        return $this->materials->whereNull('band_id');
    }

    // What's still owed by the client right now = actual billed total minus
    // what's already been collected (not the initial quote value — that's
    // just the reference point, real costs can drift from the estimate)
    public function amountDue(): float
    {
        return $this->actualClientTotal() - $this->totalCollected();
    }

    // Total collected from client = مقدمات + دفعات عقود التقسيط + التحصيلات
    // المباشرة المسجّلة من صفحة المستحقات (للمشاريع اللي مش معمولها عقد تقسيط)
    public function totalCollected(): float
    {
        return (float) $this->cached_collected;
    }

    public function computeTotalCollected(): float
    {
        $fromContracts = (float) $this->contracts->sum(
            fn ($c) => (float) $c->down_payment + (float) $c->payments->sum('amount_paid')
        );

        $fromDirect = (float) $this->clientPayments->sum(fn ($p) => (float) $p->amount + (float) $p->discount);

        return $fromContracts + $fromDirect;
    }

    // المشروع معموله عقد تقسيط؟ (عقد للمشروع كامل أو لأي بند)
    public function hasInstallmentContract(): bool
    {
        return $this->contracts->isNotEmpty();
    }

    // عقد يغطي المشروع بالكامل (مش بند محدد) — لو موجود، يمنع إضافة أي بند
    // جديد أو شراء أي خامة جديدة على المشروع كله (شايف MaterialController /
    // ProjectBandController::store)
    public function hasWholeProjectInstallmentContract(): bool
    {
        return $this->contracts()->whereNull('band_id')->exists();
    }

    // النطاق (قيمة الفوترة) اللي اتغطى بعقود التقسيط وقت إنشائها — مجموع
    // cash_price لكل عقود المشروع. أي فوترة تحصل بعد كده (خامة جديدة أو بند
    // جديد) بتزوّد actualClientTotal() من غير ما تزوّد النطاق ده، فتبقى مستحق
    // منفصل عن العقد (مش بيتلخبط مع خطة السداد بتاعته).
    public function contractedScope(): float
    {
        return (float) $this->contracts->sum(fn ($c) => (float) $c->cash_price);
    }

    // مستحق زيادة عن نطاق عقد/عقود التقسيط — فوترة جديدة اتسجّلت بعد العقد
    // (خامات/بند إضافي) لسه ما اتحصّلتش. بيتحصّل مباشرة من صفحة المستحقات، منفصل
    // تمامًا عن جدول سداد العقد.
    public function receivableExcess(): float
    {
        $gross = max(0, $this->actualClientTotal() - $this->contractedScope());
        return max(0, $gross - (float) $this->clientPayments->sum(fn ($p) => (float) $p->amount + (float) $p->discount));
    }

    // Execution progress = share of bands marked "منفذ" (done) — replaces the
    // old manually-entered current_stage/8 estimate with something that
    // actually reflects real work completed
    public function progressPct(): float
    {
        $total = $this->bands->count();
        if ($total === 0) {
            return 0;
        }

        return round($this->bands->where('status', 'done')->count() / $total * 100);
    }

    // Total spent on materials + labor for non-pending bands
    public function totalSpent(): float
    {
        return (float) $this->cached_spent;
    }

    public function computeTotalSpent(): float
    {
        $materialCost = $this->materials->sum(fn ($m) => $m->netCost());
        $laborCost    = $this->bands->whereIn('status', ['active', 'done'])->sum('labor_amount');
        return $materialCost + $laborCost;
    }

    // Updates cached values and saves
    public function recalculateCachedTotals(): void
    {
        $this->updateQuietly([
            'cached_actual_total' => $this->computeActualClientTotal(),
            'cached_collected'    => $this->computeTotalCollected(),
            'cached_spent'        => $this->computeTotalSpent(),
        ]);
    }
}
