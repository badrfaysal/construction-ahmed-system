<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One installment (دفعة) paid to a craftsman while the work is in progress.
// Only what's actually been paid hits the wallet — the rest stays a
// commitment, never a debit. See sy2_worker_payments migration.
class WorkerPayment extends Model
{
    protected $table = 'sy2_worker_payments';

    protected $fillable = [
        'band_worker_id', 'project_id', 'project_band_id', 'account_id',
        'amount', 'date', 'method', 'notes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(BandWorker::class, 'band_worker_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'project_band_id');
    }
}
