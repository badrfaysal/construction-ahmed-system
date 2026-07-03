<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One expected line item inside a quote band (e.g. "أسمنت" within "تشطيب") —
// a rough estimate at quoting time, not a real purchase record
class QuoteBandItem extends Model
{
    protected $table = 'sy2_quote_band_items';

    protected $fillable = ['quote_band_id', 'name', 'qty', 'unit_price', 'supervision_pct', 'sort_order'];

    public function band(): BelongsTo
    {
        return $this->belongsTo(QuoteBand::class, 'quote_band_id');
    }

    // Estimated total including the supervision markup — same formula as
    // Material::netClientCost() (price × (1 + supervision%))
    public function total(): float
    {
        $base = (float) $this->qty * (float) $this->unit_price;
        return $base * (1 + (float) $this->supervision_pct / 100);
    }
}
