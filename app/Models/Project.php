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

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'project_id')->orderBy('sort_order');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'project_id')->orderByDesc('date');
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
        $bandsTotal = (float) $this->bands->sum(fn ($band) => $band->actualClientTotal());
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

    // Total collected from client (paid installments only)
    public function totalCollected(): float
    {
        return (float) $this->installments->where('status', 'paid')->sum('amount');
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
        $materialCost = $this->materials->sum(fn ($m) => $m->netCost());
        $laborCost    = $this->bands->whereIn('status', ['active', 'done'])->sum('labor_amount');
        return $materialCost + $laborCost;
    }
}
