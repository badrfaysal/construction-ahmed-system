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

    // Total دفعات already paid to this craftsman
    public function paidTotal(): float
    {
        return (float) $this->payments->sum('amount');
    }

    // What's still owed to this craftsman = his contracted amount minus what
    // he's been paid so far (never negative — an overpayment reads as zero due)
    public function remaining(): float
    {
        return max((float) $this->amount - $this->paidTotal(), 0);
    }
}
