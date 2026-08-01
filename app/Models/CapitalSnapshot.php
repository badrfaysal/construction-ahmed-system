<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapitalSnapshot extends Model
{
    protected $table = 'sy2_capital_snapshots';

    protected $fillable = [
        'snapshot_date',
        'net_capital',
        'details',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'net_capital' => 'decimal:2',
        'details' => 'array',
    ];
}
