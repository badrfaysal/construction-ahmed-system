<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// سجل تدقيق ثابت — صف واحد لكل إنشاء/تعديل/حذف لأي حركة مالية. لا يُعدَّل ولا
// يُحذف أبداً بعد كتابته (append-only)، عشان "سجل الحركات" يفضل يعرض تاريخ
// كامل حتى للحركات اللي اتلغت أو اتعدّلت بعد كده. اتكتب بواسطة
// App\Observers\TransactionAuditObserver.
class AuditLog extends Model
{
    protected $table = 'sy2_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'action', 'transaction_id', 'direction', 'type', 'party', 'amount',
        'project_id', 'band_id', 'account_id', 'ref_type', 'ref_id',
        'description', 'date', 'old_values', 'performed_by', 'happened_at',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'happened_at' => 'datetime',
            'old_values'  => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'band_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function actionAr(): string
    {
        return match ($this->action) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف / إلغاء',
            default   => $this->action,
        };
    }

    public function directionAr(): string
    {
        return $this->direction === 'in' ? 'وارد' : ($this->direction === 'out' ? 'صادر' : '—');
    }
}
