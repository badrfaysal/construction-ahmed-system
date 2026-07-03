<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'sy2_suppliers';

    protected $fillable = ['name', 'phone', 'email', 'address', 'notes'];

    // All material purchases from this supplier
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'supplier_id');
    }
}
