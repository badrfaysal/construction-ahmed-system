<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// عقد تقسيط واحد مربوط بمشروع — يحمل خطة السداد (إجمالي/مقدم/شهور/قسط) والمتبقي.
// الدفعات الفعلية بتتسجّل في sy2_installment_payments وبتخصم من remaining_balance.
class InstallmentContract extends Model
{
    protected $table = 'sy2_installment_contracts';

    protected $fillable = [
        'project_id', 'band_id', 'account_id', 'customer_name', 'customer_phone', 'product_name',
        'cash_price', 'discount', 'down_payment', 'interest_rate',
        'installment_months', 'total_after_interest', 'monthly_installment',
        'due_day', 'remaining_balance', 'start_date', 'status',
        'close_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'           => 'date',
            'cash_price'           => 'decimal:2',
            'down_payment'         => 'decimal:2',
            'total_after_interest' => 'decimal:2',
            'monthly_installment'  => 'decimal:2',
            'remaining_balance'    => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // البند المتعاقد عليه (لو العقد لبند محدد) — null لو العقد للمشروع كامل
    public function band(): BelongsTo
    {
        return $this->belongsTo(ProjectBand::class, 'band_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class, 'contract_id')->orderBy('payment_date');
    }

    // ── حسابات موحّدة (بروح InstallmentFinanceService في السيستم الأول) ──

    // إجمالي اللي دفعه العميل = المقدم + كل الدفعات
    public function totalPaidByCustomer(): float
    {
        return (float) $this->down_payment + (float) $this->payments->sum('amount_paid');
    }

    // نسبة إنجاز السداد (0-100)
    public function progressPercent(): int
    {
        $total = (float) $this->total_after_interest;
        if ($total <= 0) {
            return 100;
        }
        return (int) min(100, round($this->totalPaidByCustomer() / $total * 100));
    }

    public function isPaidInFull(): bool
    {
        return (float) $this->remaining_balance <= 0.009 && ! $this->isWrittenOff();
    }

    public function isWrittenOff(): bool
    {
        return $this->close_reason === 'written_off';
    }

    // القسط المستحق لهذا الشهر مدفوع؟ (مجموع دفعات الشهر الحالي ≥ القسط الشهري)
    public function paidThisMonthAmount(): float
    {
        $ym = now()->format('Y-m');
        return (float) $this->payments
            ->filter(fn ($p) => $p->payment_date && $p->payment_date->format('Y-m') === $ym)
            ->sum('amount_paid');
    }

    public function isPaidThisMonth(): bool
    {
        return $this->paidThisMonthAmount() >= (float) $this->monthly_installment * 0.99
            && (float) $this->monthly_installment > 0;
    }

    // جدول الأقساط المشتق (مش مخزّن صفوف) — شهر 1..N بتاريخ الاستحقاق والمبلغ،
    // وحالة كل قسط محسوبة من إجمالي المدفوع بالتدريج
    public function schedule(): array
    {
        $months = (int) $this->installment_months;
        if ($months <= 0) {
            return [];
        }

        $paidPool = (float) $this->payments->sum('amount_paid'); // المدفوع بعد المقدم
        $monthly  = (float) $this->monthly_installment;
        $start    = $this->start_date ? $this->start_date->copy() : now();
        $dueDay   = (int) $this->due_day ?: $start->day;

        $rows = [];
        for ($i = 1; $i <= $months; $i++) {
            $due = $start->copy()->addMonths($i);
            // اضبط يوم الاستحقاق (مع مراعاة الشهور القصيرة)
            $due->day(min($dueDay, $due->daysInMonth));

            $covered = min($paidPool, $monthly);
            $paidPool = max(0, $paidPool - $monthly);

            $status = $covered >= $monthly - 0.01 ? 'paid'
                : ($covered > 0 ? 'partial'
                : ($due->isPast() ? 'due' : 'upcoming'));

            $rows[] = [
                'no'        => $i,
                'due_date'  => $due,
                'amount'    => round($monthly, 2),
                'paid'      => round($covered, 2),
                'status'    => $status,
            ];
        }

        return $rows;
    }
}
