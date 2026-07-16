<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDiscount extends Model
{
    protected $table = 'sy2_project_discounts';

    protected $fillable = [
        'project_id',
        'amount',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
