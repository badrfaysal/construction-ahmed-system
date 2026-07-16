<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialInvoice extends Model
{
    protected $table = 'sy2_material_invoices';

    protected $fillable = [
        'project_id',
        'supplier_id',
        'account_id',
        'date',
        'name',
        'total_amount',
        'paid_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'invoice_id');
    }

    public function remainingBalance(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingBalance() <= 0;
    }

    public function syncTotalAmount(): void
    {
        $this->total_amount = $this->materials->sum(fn ($m) => tap($m, fn() => $m->recalculateCost())->grossCost());
        $this->save();
    }
}
