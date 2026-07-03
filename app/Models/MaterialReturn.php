<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One return against a specific material purchase — a purchase can have
// several of these added over time, instead of a single fixed returned_qty
class MaterialReturn extends Model
{
    protected $table = 'sy2_material_returns';

    protected $fillable = ['material_id', 'qty', 'date', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
