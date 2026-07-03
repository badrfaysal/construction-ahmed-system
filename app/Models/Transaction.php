<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'sy2_transactions';

    protected $fillable = [
        'project_id', 'band_id', 'direction', 'type',
        'party', 'amount', 'date', 'description', 'ref_type', 'ref_id',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'band_id');
    }

    // Arabic label for the direction used in the UI
    public function directionAr(): string
    {
        return $this->direction === 'in' ? 'وارد' : 'صادر';
    }
}
