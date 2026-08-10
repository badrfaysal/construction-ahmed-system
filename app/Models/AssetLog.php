<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    protected $table = 'sy2_asset_logs';

    protected $fillable = [
        'asset_id',
        'action', // purchase, depreciation, edit, sale
        'amount',
        'notes',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
