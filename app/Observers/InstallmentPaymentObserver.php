<?php

namespace App\Observers;

use App\Models\InstallmentPayment;
use App\Models\Transaction;

// يبقّي المحفظة (accounts id=37) وسجل الحركات متزامنين مع دفعات الأقساط:
// كل دفعة تحصيل = حركة "in" في sy2_transactions (بتزوّد المحفظة عبر
// TransactionObserver)، وحذف الدفعة بيعكس التحصيل. كمان بيخصم/يرجّع متبقّي العقد.
// كل العمليات على MODEL instance عشان أحداث Eloquent تشتغل (زي ما هو مذكور في
// wallet-treasury-integration).
class InstallmentPaymentObserver
{
    public function created(InstallmentPayment $payment): void
    {
        // اخصم من متبقّي العقد
        $contract = $payment->contract;
        if ($contract) {
            $contract->decrement('remaining_balance', (float) $payment->amount_paid + (float) $payment->discount_applied);
        }

        // حركة تحصيل واردة تغذّي المحفظة
        Transaction::create([
            'project_id'  => $payment->project_id,
            'account_id'  => $payment->account_id,
            'direction'   => 'in',
            'type'        => 'تحصيل قسط',
            'party'       => $contract?->customer_name,
            'amount'      => $payment->amount_paid,
            'date'        => $payment->payment_date,
            'description' => 'دفعة على عقد #' . $payment->contract_id . ($payment->notes ? ' — ' . $payment->notes : ''),
            'ref_type'    => 'inst_payment',
            'ref_id'      => $payment->id,
        ]);
    }

    public function deleted(InstallmentPayment $payment): void
    {
        // رجّع المتبقي على العقد
        $contract = $payment->contract;
        if ($contract) {
            $contract->increment('remaining_balance', (float) $payment->amount_paid + (float) $payment->discount_applied);
        }

        // احذف حركة التحصيل (Model instance عشان TransactionObserver يعكس المحفظة)
        Transaction::where('ref_type', 'inst_payment')->where('ref_id', $payment->id)->first()?->delete();
    }
}
