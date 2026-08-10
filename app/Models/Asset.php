<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'sy2_assets';

    protected $fillable = [
        'name',
        'purchase_cost',
        'current_value',
        'purchase_date',
        'notes',
        'status',
        'account_id',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class, 'asset_id')->orderBy('created_at', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeFullyDepreciated($query)
    {
        return $query->where('status', 'fully_depreciated');
    }
}
