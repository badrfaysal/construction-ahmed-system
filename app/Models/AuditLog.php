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

    // لون وأيقونة ثابتين لكل نوع حركة (حسب ref_type) — عشان العين تفرّق فورًا
    // بين نوع وتاني في سجل الحركات من غير ما تقرا النص
    public function refMeta(): array
    {
        return match ($this->ref_type) {
            'material'       => ['label' => 'شراء خامة',       'color' => '#f59e0b', 'icon' => 'i-box'],
            'return'         => ['label' => 'مرتجع خامة',       'color' => '#0d9488', 'icon' => 'i-box'],
            'debt'           => ['label' => 'سداد دين مورد',    'color' => '#dc2626', 'icon' => 'i-credit-card'],
            'worker_payment' => ['label' => 'دفعة صنايعي',       'color' => '#8b5cf6', 'icon' => 'i-hardhat'],
            'inst_payment'   => ['label' => 'تحصيل قسط',         'color' => '#2563eb', 'icon' => 'i-calendar'],
            'inst_down'      => ['label' => 'مقدم عقد تقسيط',    'color' => '#1d4ed8', 'icon' => 'i-receipt'],
            'client_payment' => ['label' => 'تحصيل من العميل',   'color' => '#16a34a', 'icon' => 'i-cash'],
            'manual'         => ['label' => 'حركة محفظة يدوية',  'color' => '#c9821a', 'icon' => 'i-wallet'],
            'band'           => ['label' => 'أجور فنيين (قديم)', 'color' => '#8b5cf6', 'icon' => 'i-hardhat'],
            default          => ['label' => 'حركة عامة',         'color' => '#64748b', 'icon' => 'i-activity'],
        };
    }
}
