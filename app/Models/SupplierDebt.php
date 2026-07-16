<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDebt extends Model
{
    protected $table = 'sy2_supplier_debts';

    protected $fillable = [
        'project_id', 'band_id', 'supplier_id', 'material_id',
        'description', 'total_amount', 'paid_amount', 'due_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'band_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MaterialInvoice::class, 'invoice_id');
    }

    // Remaining debt
    public function remaining(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }

    public function statusAr(): string
    {
        return match ($this->status) {
            'pending' => 'معلق',
            'partial' => 'جزئي',
            'paid'    => 'مسدد',
            default   => $this->status,
        };
    }

    public function statusTag(): string
    {
        return match ($this->status) {
            'paid'    => 'green',
            'partial' => 'amber',
            default   => 'red',
        };
    }

    // Flag debts overdue today
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }
}
