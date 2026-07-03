<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteBand extends Model
{
    protected $table = 'sy2_quote_bands';

    protected $fillable = ['quote_id', 'name', 'price', 'sort_order'];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteBandItem::class, 'quote_band_id')->orderBy('sort_order');
    }

    // Sum of the itemized breakdown — this is what `price` gets synced to
    // whenever the band has items (see QuoteController::store/update)
    public function itemsTotal(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->total());
    }
}
