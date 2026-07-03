<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'sy2_materials';

    protected $fillable = [
        'project_id', 'band_id', 'supplier_id', 'category',
        'item', 'unit', 'qty', 'unit_price', 'sell_price', 'supervision_pct',
        'date', 'payment_status', 'paid_amount',
    ];

    // A miscellaneous expense (نثري) — tips, transport, meals — vs a real
    // material purchase. Both are billed to the client the same way.
    public function isMisc(): bool
    {
        return $this->category === 'misc';
    }

    protected function casts(): array
    {
        return ['date' => 'date'];
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

    public function returns(): HasMany
    {
        return $this->hasMany(MaterialReturn::class, 'material_id');
    }

    // Sum of every return recorded against this purchase, added at any time —
    // replaces the old fixed returned_qty snapshot
    public function returnedQty(): float
    {
        return (float) $this->returns->sum('qty');
    }

    // Net purchased quantity after returns
    public function netQty(): float
    {
        return (float) $this->qty - $this->returnedQty();
    }

    // Net cost = net quantity × unit price (purchase price — our real cost)
    public function netCost(): float
    {
        return $this->netQty() * (float) $this->unit_price;
    }

    // Per-unit price charged to the client: sell price (falls back to purchase
    // price if not set) plus the supervision markup on top
    public function clientUnitPrice(): float
    {
        $base = (float) ($this->sell_price ?? $this->unit_price);
        return $base * (1 + (float) $this->supervision_pct / 100);
    }

    // Net amount billed to the client for this line item
    public function netClientCost(): float
    {
        return $this->netQty() * $this->clientUnitPrice();
    }

    // Company profit on this line = what the client pays minus what we paid
    public function profit(): float
    {
        return $this->netClientCost() - $this->netCost();
    }
}
