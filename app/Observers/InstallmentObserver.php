<?php

namespace App\Observers;

use App\Models\Installment;
use App\Models\Transaction;

// Keeps سجل الحركات in sync with installment payments automatically —
// an "in" transaction only exists for installments that are actually marked paid
class InstallmentObserver
{
    // An installment created directly with status=paid also needs its transaction
    public function created(Installment $installment): void
    {
        if ($installment->status === 'paid') {
            $this->createTransaction($installment);
        }
    }

    // Sync the transaction when the paid status (or amount/date) changes
    public function updated(Installment $installment): void
    {
        $existing = Transaction::where('ref_type', 'installment')->where('ref_id', $installment->id)->first();

        if ($installment->status === 'paid') {
            if ($existing) {
                $existing->update([
                    'amount' => $installment->amount,
                    'date'   => $installment->paid_date ?? $installment->due_date,
                    'party'  => $installment->project->client->name,
                ]);
            } else {
                $this->createTransaction($installment);
            }
        } elseif ($existing) {
            // Status was changed away from "paid" — the collection no longer happened
            $existing->delete();
        }
    }

    public function deleted(Installment $installment): void
    {
        // Model instance (not query-builder) so TransactionObserver fires and
        // reverses the wallet credit for this collection.
        Transaction::where('ref_type', 'installment')->where('ref_id', $installment->id)->first()?->delete();
    }

    private function createTransaction(Installment $installment): void
    {
        Transaction::create([
            'project_id'  => $installment->project_id,
            'direction'   => 'in',
            'type'        => 'تحصيل من عميل',
            'party'       => $installment->project->client->name,
            'amount'      => $installment->amount,
            'date'        => $installment->paid_date ?? $installment->due_date,
            'description' => $installment->label,
            'ref_type'    => 'installment',
            'ref_id'      => $installment->id,
        ]);
    }
}
