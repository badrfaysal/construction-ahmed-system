<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'sy2_transactions';

    protected $fillable = [
        'project_id', 'band_id', 'account_id', 'direction', 'type',
        'party', 'amount', 'discount', 'date', 'description', 'ref_type', 'ref_id',
    ];

    // المحفظة اللي الحركة اتحرّكت منها/عليها (من جدول accounts) — للعرض
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

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

    /**
     * صافي الكاش بتاع المقاولات عبر كل الحسابات (من جدول الحركات الخاص بنا)
     */
    public static function constructionNetCash(bool $activeOnly = false): float
    {
        $query = static::query()->where('ref_type', '!=', 'manual');

        if ($activeOnly) {
            $query->whereHas('project', function ($q) {
                $q->whereNotIn('status', ['done', 'canceled']);
            });
        }

        return (float) $query->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN direction = 'in' THEN amount ELSE -amount END"));
    }
}
