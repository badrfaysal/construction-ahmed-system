<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marketer extends Model
{
    protected $table = 'sy2_marketers';

    protected $fillable = ['name', 'phone'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'ref_id')->where('ref_type', 'marketer_commission');
    }

    public function totalPaid(): float
    {
        return (float) $this->transactions->sum('amount');
    }

    public function projectsCount(): int
    {
        return $this->transactions->pluck('project_id')->filter()->unique()->count();
    }
}
