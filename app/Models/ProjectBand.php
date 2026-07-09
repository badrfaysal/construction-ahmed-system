<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

// A "band" (بند) is one phase of construction work within a project
// e.g. "محارة", "سيراميك وأرضيات", "دهانات"
class ProjectBand extends Model
{
    protected $table = 'sy2_project_bands';

    protected $fillable = [
        'project_id', 'name', 'client_price', 'status',
        'contract_type', 'contract_qty', 'contract_unit_rate', 'labor_sell_rate',
        'team_name', 'labor_amount', 'labor_sell_price',
        'labor_supervision_pct', 'labor_date', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['labor_date' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'band_id');
    }

    public function workers(): HasMany
    {
        return $this->hasMany(BandWorker::class, 'project_band_id')->orderBy('sort_order');
    }

    public function installmentContracts(): HasMany
    {
        return $this->hasMany(InstallmentContract::class, 'band_id');
    }

    // البند ده معموله عقد تقسيط بنفسه؟ لو أيوه، يُمنع تسجيل أي خامة جديدة عليه
    // (اعمل بند جديد باسم مختلف بدل ما تخلط فوترة خامة جديدة مع عقد قايم)
    public function hasInstallmentContract(): bool
    {
        return $this->installmentContracts()->exists();
    }

    // Sum of what each itemized worker is paid — when workers exist, this
    // drives the band's labor_amount instead of the simple contract fields
    public function workersTotal(): float
    {
        return (float) $this->workers->sum('amount');
    }

    public function contractTypeAr(): string
    {
        return match ($this->contract_type) {
            'lump_sum'  => 'مقاولة مقطوعة',
            'daily'     => 'يومية',
            'per_meter' => 'بالمتر',
            'per_piece' => 'بالقطعة',
            default     => '—',
        };
    }

    // Syncs this band's workers with the submitted list — every band's labor
    // is always a list of technicians now (one entry minimum in practice).
    // Each worker's cost (amount) and client billing (sell_amount) are
    // computed server-side from qty×rate (per_meter/per_piece/daily) or
    // entered directly (lump_sum), then the band's totals are the sum of all
    // of them — called after every create/update so re-editing always
    // recalculates from scratch and never compounds.
    //
    // Rows carrying an id are UPDATED in place (never delete+recreate): each
    // worker's دفعات hang off his row with a cascading FK, so recreating him
    // would silently wipe his whole payment history on every band edit.
    public function syncLabor(array $workersInput): void
    {
        $keptIds = [];

        foreach (array_values($workersInput) as $i => $w) {
            $type = $w['contract_type'] ?? null;
            $qty  = $w['contract_qty'] ?? null;

            $attrs = [
                'name'               => $w['name'],
                'phone'              => $w['phone'] ?? null,
                'specialty'          => $w['specialty'] ?? null,
                'contract_type'      => $type,
                'contract_qty'       => $qty,
                'contract_unit_rate' => $w['contract_unit_rate'] ?? null,
                'sell_rate'          => $w['sell_rate'] ?? null,
                'amount'             => static::computeContractAmount($type, $qty, $w['contract_unit_rate'] ?? null, $w['amount'] ?? 0),
                'sell_amount'        => static::computeContractAmount($type, $qty, $w['sell_rate'] ?? null, $w['sell_amount'] ?? 0),
                'supervision_pct'    => $w['supervision_pct'] ?? 0,
                'start_date'         => $w['start_date'] ?? null,
                'notes'              => $w['notes'] ?? null,
                'sort_order'         => $i,
            ];

            // Lookup scoped to this band's own workers, so a forged foreign id
            // can never hijack another band's row — it just creates a new one.
            $existing = ! empty($w['id']) ? $this->workers()->whereKey($w['id'])->first() : null;

            if ($existing) {
                $existing->update($attrs);
                $keptIds[] = $existing->id;
            } else {
                $keptIds[] = $this->workers()->create($attrs)->id;
            }
        }

        // Workers dropped from the form. A craftsman with recorded دفعات can't
        // be silently removed — his payment history (and the wallet debits
        // behind it) must stay traceable. The user flow for "صنايعي مشي" is to
        // keep him with his actual executed qty and add a new worker instead.
        foreach ($this->workers()->whereNotIn('id', $keptIds)->get() as $removed) {
            if ($removed->payments()->exists()) {
                throw ValidationException::withMessages([
                    'workers' => 'الصنايعي "' . $removed->name . '" له دفعات مسجلة فلا يمكن حذفه من البند — لو مشي بدري، قلّل كميته/أجره وسيبه في القائمة وضيف صنايعي جديد يكمّل. (أو احذف دفعاته الأول من صفحة الدفعات.)',
                ]);
            }
            $removed->delete();
        }

        $this->load('workers');

        if (count($workersInput)) {
            $laborAmount = $this->workersTotal();
            // The client markup is already applied per worker, so the band-level
            // supervision % is reset to avoid applying it a second time on top
            $laborSellPrice = $this->workers->sum(fn ($w) => $w->clientPrice());
            $laborSupervisionPct = 0;
        } else {
            // No workers yet (blank new band, or a legacy band not resaved
            // since this feature shipped) — fall back to the old band-level
            // simple contract fields so nothing regresses to zero silently
            $laborAmount = static::computeContractAmount($this->contract_type, $this->contract_qty, $this->contract_unit_rate, $this->labor_amount);
            $laborSellPrice = $this->labor_sell_price;
            $laborSupervisionPct = $this->labor_supervision_pct;
        }

        $this->update([
            'labor_amount'          => $laborAmount,
            'labor_sell_price'      => $laborSellPrice,
            'labor_supervision_pct' => $laborSupervisionPct,
        ]);
    }

    // Recomputes the band's labor totals straight from its current workers —
    // used when workers are changed outside the band edit form (e.g. تبديل الفني
    // on the payments screen). Mirrors the tail of syncLabor(): the client
    // markup is already baked into each worker's clientPrice(), so the
    // band-level supervision % is zeroed to avoid double-counting it.
    public function recomputeLaborTotals(): void
    {
        $this->load('workers');

        $this->update([
            'labor_amount'          => $this->workersTotal(),
            'labor_sell_price'      => $this->workers->sum(fn ($w) => $w->clientPrice()),
            'labor_supervision_pct' => 0,
        ]);
    }

    // For bands created before the "workers list" model existed — builds a
    // synthetic single-worker seed from the old team_name/labor_amount fields
    // so opening the edit form shows a smooth starting point instead of a
    // blank screen. Nothing is written to the database until the form is saved.
    public function legacyWorkerSeed(): ?array
    {
        if ($this->workers->isNotEmpty()) {
            return null;
        }
        if (! $this->team_name && (float) $this->labor_amount <= 0) {
            return null;
        }

        return [
            'name'            => $this->team_name ?: 'فني 1',
            'contract_type'   => 'lump_sum',
            'amount'          => (float) $this->labor_amount,
            'sell_amount'     => (float) ($this->labor_sell_price ?? $this->labor_amount),
            'supervision_pct' => (float) $this->labor_supervision_pct,
        ];
    }

    // amount = qty × rate for daily/per_meter/per_piece contracts, or the
    // manually entered fallback amount otherwise (lump_sum, or no type set)
    public static function computeContractAmount(?string $type, $qty, $rate, $fallback): float
    {
        if (in_array($type, ['daily', 'per_meter', 'per_piece'], true) && $qty !== null && $rate !== null) {
            return (float) $qty * (float) $rate;
        }

        return (float) ($fallback ?? 0);
    }

    // Total دفعات paid across all this band's craftsmen so far
    public function laborPaid(): float
    {
        return (float) $this->workers->sum(fn ($w) => $w->paidTotal());
    }

    // What's still owed to this band's craftsmen (contracted labor minus paid)
    public function laborRemaining(): float
    {
        return (float) $this->workers->sum(fn ($w) => $w->remaining());
    }

    // Net material cost for this band (purchased - returned) — our real cost
    public function materialCost(): float
    {
        return (float) $this->materials->sum(fn ($m) => $m->netCost());
    }

    // Total cost of this band (materials + labor) — our real cost
    public function totalCost(): float
    {
        return (float) $this->cached_total_cost;
    }

    public function computeTotalCost(): float
    {
        return $this->materialCost() + (float) $this->labor_amount;
    }

    // Labor price charged to the client: sell price (falls back to the amount
    // actually paid if not set) plus the supervision markup on top
    public function laborClientPrice(): float
    {
        $base = (float) ($this->labor_sell_price ?? $this->labor_amount);
        return $base * (1 + (float) $this->labor_supervision_pct / 100);
    }

    // Sum of what's billed to the client for this band's materials
    public function materialClientCost(): float
    {
        return (float) $this->materials->sum(fn ($m) => $m->netClientCost());
    }

    // Actual amount billed to the client for this band (materials + labor,
    // at their real sell prices) — this is what drives the client statement,
    // separate from the pre-agreed client_price from the quote
    public function actualClientTotal(): float
    {
        return (float) $this->cached_actual_total;
    }

    public function computeActualClientTotal(): float
    {
        return $this->materialClientCost() + $this->laborClientPrice();
    }

    // Updates cached values and cascades up to the parent project
    public function recalculateCachedTotals(): void
    {
        $this->updateQuietly([
            'cached_actual_total' => $this->computeActualClientTotal(),
            'cached_total_cost'   => $this->computeTotalCost(),
        ]);

        if ($this->project) {
            $this->project->recalculateCachedTotals();
        }
    }

    // Company profit for this band = what the client actually pays minus our real cost
    public function profit(): float
    {
        return $this->actualClientTotal() - $this->totalCost();
    }
}
