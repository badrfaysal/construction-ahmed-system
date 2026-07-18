<?php

namespace App\Observers;

use App\Models\InstallmentContract;
use App\Models\Transaction;

// يزامن المحفظة مع العقد نفسه: المقدم المدفوع وقت الإنشاء = حركة "in"،
// وعند حذف العقد بنعكس المقدم + كل دفعاته يدويًا (لأن حذف الـ DB cascade
// مبيشغّلش أحداث Eloquent فالمحفظة مكانتش هتترجع).
//
// ملحوظة مهمة: فورم إنشاء العقد بيملي "المقدم" تلقائيًا من فلوس اتحصّلت
// فعلاً من العميل قبل كده (مسجّلة أصلاً كـ client_payment) — مش فلوس جديدة.
// لو خلقنا حركة "in" تانية بمبلغ المقدم، هيتضاعف نفس التحصيل في المحفظة
// وفي إجمالي المحصّل. عشان كده InstallmentController::store() بيحط علامة
// skipDownPaymentTransaction على العقد قبل الحفظ لو المقدم ده أصلاً معاد
// تصنيفه من تحصيل سابق — الحركة الوحيدة اللي بتتعمل فعلاً هنا هي لمقدم
// "جديد" حقيقي (نادر مع الفورم الحالي اللي بيقفل الحقل دايمًا على المحصّل).
class InstallmentContractObserver
{
    public function created(InstallmentContract $contract): void
    {
        $actualAmount = (empty($contract->skipDownPaymentTransaction)) ? (float) $contract->down_payment : 0;
        
        Transaction::create([
            'project_id'  => $contract->project_id,
            'account_id'  => $contract->account_id,
            'direction'   => 'in',
            'type'        => 'إنشاء عقد تقسيط',
            'party'       => $contract->customer_name,
            'amount'      => $actualAmount,
            'date'        => $contract->start_date ?? now(),
            'description' => 'عقد تقسيط — ' . $contract->product_name . ($contract->down_payment > 0 && $actualAmount == 0 ? ' (المقدم ' . (float)$contract->down_payment . ' مسجل مسبقاً)' : ''),
            'ref_type'    => 'inst_down',
            'ref_id'      => $contract->id,
        ]);

        $contract->project?->recalculateCachedTotals();
    }

    public function deleting(InstallmentContract $contract): void
    {
        // اعكس كل دفعة كـ MODEL instance (يشغّل observer الدفعة → يرجّع المحفظة)
        foreach ($contract->payments()->get() as $payment) {
            $payment->delete();
        }

        // احذف حركة المقدم
        Transaction::where('ref_type', 'inst_down')->where('ref_id', $contract->id)->first()?->delete();

        $contract->project?->recalculateCachedTotals();
    }
}
