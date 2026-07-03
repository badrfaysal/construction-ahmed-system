<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    protected $table = 'sy2_warranties';

    protected $fillable = ['project_id', 'start_date', 'months'];

    protected function casts(): array
    {
        return ['start_date' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(WarrantyComplaint::class, 'warranty_id')->orderByDesc('date');
    }

    // Date warranty expires
    public function expiresAt(): \Carbon\Carbon
    {
        return $this->start_date->addMonths($this->months);
    }

    // Whether the warranty is still active today
    public function isActive(): bool
    {
        return $this->expiresAt()->isFuture();
    }
}
