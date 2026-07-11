<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One estimated technician inside a quote band — mirrors BandWorker, but this
// is just a plan at quoting time, not a real hire. Copied into a real
// BandWorker row when the quote is converted into a project.
class QuoteBandWorker extends Model
{
    protected $table = 'sy2_quote_band_workers';

    protected $fillable = [
        'quote_band_id', 'name', 'specialty',
        'contract_type', 'contract_qty', 'contract_unit_rate', 'sell_rate',
        'amount', 'sell_amount', 'supervision_pct', 'notes', 'sort_order',
    ];

    public function band(): BelongsTo
    {
        return $this->belongsTo(QuoteBand::class, 'quote_band_id');
    }

    // What the client is billed for this worker's share — same fallback/markup
    // formula as BandWorker::clientPrice()
    public function clientPrice(): float
    {
        $base = (float) $this->sell_amount ?: (float) $this->amount;
        return $base * (1 + (float) $this->supervision_pct / 100);
    }
}
