<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Supplier extends Model
{
    use LogsActivity;

    protected $table = 'sy2_suppliers';

    protected $fillable = ['name', 'activity', 'phone', 'email', 'address', 'notes'];

    // All material purchases from this supplier
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'supplier_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(MaterialInvoice::class, 'supplier_id');
    }
}
