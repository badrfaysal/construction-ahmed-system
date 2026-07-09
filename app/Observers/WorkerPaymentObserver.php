<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\WorkerPayment;

// Every دفعة paid to a craftsman is real cash out the door the moment it's
// recorded — so each one books an "out" entry in سجل الحركات that debits
// محفظة المقاولات (blocked by TransactionObserver if the balance can't cover
// it). Deleting a payment reverses it. Labor is NEVER auto-debited in full;
// only these actual payments move the wallet (see ProjectBandObserver).
class WorkerPaymentObserver
{
    public function created(WorkerPayment $payment): void
    {
        $worker = $payment->worker()->first();

        Transaction::create([
            'project_id'  => $payment->project_id,
            'band_id'     => $payment->project_band_id,
            'account_id'  => $payment->account_id,
            'direction'   => 'out',
            'type'        => 'دفعة صنايعي',
            'party'       => $worker?->name ?? 'صنايعي',
            'amount'      => $payment->amount,
            'date'        => $payment->date,
            'description' => 'دفعة مصنعية' . ($worker ? ' — ' . $worker->name : ''),
            'ref_type'    => 'worker_payment',
            'ref_id'      => $payment->id,
        ]);

        $payment->band?->recalculateCachedTotals();
    }

    public function deleted(WorkerPayment $payment): void
    {
        // Model instance (not query-builder) so TransactionObserver fires and
        // credits the wallet back for this reversed payment.
        Transaction::where('ref_type', 'worker_payment')->where('ref_id', $payment->id)->first()?->delete();

        $payment->band?->recalculateCachedTotals();
    }
}
