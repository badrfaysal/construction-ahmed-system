<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// One technician working a band, with their own specialty, wage and client
// billing — every band's labor is a list of these, one row per technician
class BandWorker extends Model
{
    protected $table = 'sy2_band_workers';

    protected $fillable = [
        'project_band_id', 'name', 'phone', 'specialty',
        'contract_type', 'contract_qty', 'contract_unit_rate', 'sell_rate',
        'amount', 'sell_amount', 'supervision_pct', 'start_date', 'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date'];
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'project_band_id');
    }

    // Installments (دفعات) actually paid to this craftsman over time
    public function payments(): HasMany
    {
        return $this->hasMany(WorkerPayment::class, 'band_worker_id')->orderByDesc('date');
    }

    // المصروفات/النثريات اللي الصنايعي اشتراها أو صرفها من جيبه (مسجلة כآجل) والمفروض نسددها له
    public function deferredExpenses(): HasMany
    {
        return $this->hasMany(Material::class, 'band_worker_id')
                    ->where('category', 'misc')
                    ->where('payment_status', 'deferred');
    }

    public function contractTypeAr(): string
    {
        return match ($this->contract_type) {
            'lump_sum'  => 'مقاولة مقطوعة',
            'daily'     => 'يومية', // legacy value, no longer offered in the form
            'per_meter' => 'بالمتر',
            'per_piece' => 'بالقطعة',
            default     => '—',
        };
    }

    // What the client is billed for this worker's share of the job — falls
    // back to the cost amount (no markup) if no sell amount was ever entered,
    // so leaving the field blank never silently bills the client zero
    public function clientPrice(): float
    {
        $base = (float) $this->sell_amount ?: (float) $this->amount;
        return $base * (1 + (float) $this->supervision_pct / 100);
    }

    // كل ما اتسوّى من تعاقده = كاش مدفوع + خصومات. الخصم بيسوّي جزء من التعاقد
    // زي الكاش بالظبط (بس من غير ما يطلع فلوس من المحفظة).
    public function paidTotal(): float
    {
        return (float) $this->payments->sum(fn ($p) => $p->amount + $p->discount);
    }

    // إجمالي المصروفات الآجلة اللي الصنايعي صرفها ولسه ما اخدهاش كاش
    public function deferredExpensesTotal(): float
    {
        return (float) $this->deferredExpenses->sum(fn($m) => $m->netCost());
    }

    // اللي لسه مستحق للصنايعي (علينا) = تعاقده + مصروفاته الآجلة ناقص اللي اتسوّى — never negative
    public function remaining(): float
    {
        return max((float) $this->amount + $this->deferredExpensesTotal() - $this->paidTotal(), 0);
    }

    // اللي مستحق لينا عند الصنايعي (دين عليه): لو الخصومات اللي عملناها له
    // زوّدت المسوّى فوق قيمة تعاقده + مصروفاته
    public function owedToUs(): float
    {
        return max($this->paidTotal() - ((float) $this->amount + $this->deferredExpensesTotal()), 0);
    }

    // نفس base الاستخدام في clientPrice() — عشان tradeProfit()+percentageProfit()
    // يفضلوا بالظبط بيساووا الفرق بين clientPrice() و amount (من غير drift)
    private function baseSellAmount(): float
    {
        return (float) $this->sell_amount ?: (float) $this->amount;
    }

    // الربح التجاري = فرق سعره للعميل عن أجره الفعلي، من غير نسبة الإشراف
    public function tradeProfit(): float
    {
        return $this->baseSellAmount() - (float) $this->amount;
    }

    // ربح النسبة = نسبة الإشراف المضافة فوق سعره للعميل
    public function percentageProfit(): float
    {
        return $this->baseSellAmount() * ((float) $this->supervision_pct / 100);
    }
}
