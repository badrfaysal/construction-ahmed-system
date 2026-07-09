<?php

namespace App\Observers;

use App\Models\InstallmentContract;
use App\Models\Transaction;

// يزامن المحفظة مع العقد نفسه: المقدم المدفوع وقت الإنشاء = حركة "in"،
// وعند حذف العقد بنعكس المقدم + كل دفعاته يدويًا (لأن حذف الـ DB cascade
// مبيشغّلش أحداث Eloquent فالمحفظة مكانتش هتترجع).
class InstallmentContractObserver
{
    public function created(InstallmentContract $contract): void
    {
        if ((float) $contract->down_payment > 0) {
            Transaction::create([
                'project_id'  => $contract->project_id,
                'account_id'  => $contract->account_id,
                'direction'   => 'in',
                'type'        => 'تحصيل مقدم',
                'party'       => $contract->customer_name,
                'amount'      => $contract->down_payment,
                'date'        => $contract->start_date ?? now(),
                'description' => 'مقدم عقد تقسيط — ' . $contract->product_name,
                'ref_type'    => 'inst_down',
                'ref_id'      => $contract->id,
            ]);
        }

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
