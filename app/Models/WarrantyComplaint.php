<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyComplaint extends Model
{
    protected $table = 'sy2_warranty_complaints';

    protected $fillable = ['warranty_id', 'date', 'description', 'status'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class, 'warranty_id');
    }
}
