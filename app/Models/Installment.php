<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $table = 'sy2_installments';

    protected $fillable = [
        'project_id', 'band_id', 'label', 'due_date', 'amount',
        'status', 'payment_method', 'paid_date', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'due_date'  => 'date',
            'paid_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'band_id');
    }

    // Human-readable status in Arabic for display in views
    public function statusAr(): string
    {
        return match ($this->status) {
            'paid'     => 'مدفوع',
            'due'      => 'مستحق',
            'upcoming' => 'قادم',
            default    => $this->status,
        };
    }

    // CSS tag class used in the UI (green/amber/gray)
    public function statusTag(): string
    {
        return match ($this->status) {
            'paid'     => 'green',
            'due'      => 'amber',
            default    => 'gray',
        };
    }
}
