<?php

namespace App\Observers;

use App\Models\MaterialReturn;
use App\Models\SupplierDebt;
use App\Models\Transaction;

// A return sends goods (and their value) back. It's booked as its OWN entry in
// سجل الحركات — a "وارد / مرتجع خامات" credit that puts the paid cash back into
// محفظة المقاولات — instead of silently shrinking the original purchase line.
// The unpaid (deferred) share of a return instead lowers what we still owe the
// supplier. Everything goes through the Transaction model so its observer fires
// and the wallet stays in lockstep.
class MaterialReturnObserver
{
    // Adding a return → refund the paid part to the wallet, drop the rest of the debt.
    public function created(MaterialReturn $return): void
    {
        $material = $return->material()->with(['supplier', 'invoice'])->first();
        if (! $material) {
            return;
        }

        $debt = $material->invoice_id
            ? SupplierDebt::where('invoice_id', $material->invoice_id)->first()
            : SupplierDebt::where('material_id', $material->id)->first();

        $debtRemaining = $debt ? max(0.0, (float) $debt->remaining()) : 0.0;
        $returnPrice   = $return->effectivePrice();
        $returnTotal   = round((float) $return->qty * (float) $returnPrice, 2);

        // First reduce unpaid debt if any exists; anything beyond that is cash refund to wallet
        $debtDrop   = min($debtRemaining, $returnTotal);
        $cashRefund = round($returnTotal - $debtDrop, 2);

        $accountId = $material->account_id 
            ?? $material->invoice?->account_id 
            ?? \App\Models\Account::WALLET_ID;

        Transaction::create([
            'project_id'  => $material->project_id,
            'band_id'     => $material->band_id,
            'account_id'  => $accountId,
            'direction'   => 'in',
            'type'        => 'مرتجع خامات',
            'party'       => $material->supplier?->name ?? $material->item,
            'amount'      => $cashRefund,
            'date'        => $return->date,
            'description' => 'مرتجع ' . $material->item . ' — ' . number_format($return->qty, 1) . ' ' . $material->unit,
            'ref_type'    => 'return',
            'ref_id'      => $return->id,
        ]);

        if ($debtDrop > 0) {
            $this->adjustDebt($material->id, -$debtDrop);
        }

        $material->band?->recalculateCachedTotals();
        $material->project?->recalculateCachedTotals();
    }

    // Removing a return → reverse the refund (re-debits the wallet, blocked by
    // TransactionObserver if the balance can no longer cover it) and restore
    // the debt that the return had cancelled.
    public function deleted(MaterialReturn $return): void
    {
        $tx = Transaction::where('ref_type', 'return')->where('ref_id', $return->id)->first();
        $cashRefunded = $tx ? (float) $tx->amount : 0.0;
        $tx?->delete();

        $material = $return->material()->first();
        if (! $material) {
            return;
        }

        $returnTotal = round((float) $return->qty * (float) $return->effectivePrice(), 2);
        // The debt dropped was whatever wasn't refunded in cash
        $debtDrop = max(0.0, round($returnTotal - $cashRefunded, 2));

        if ($debtDrop > 0) {
            $this->adjustDebt($material->id, $debtDrop);
        }

        $material->band?->recalculateCachedTotals();
        $material->project?->recalculateCachedTotals();
    }

    // Nudge the outstanding supplier debt for a material by $delta (negative to
    // lower it on a return, positive to restore it if that return is deleted).
    // Never drops the total below what's already been paid on the debt.
    private function adjustDebt(int $materialId, float $delta): void
    {
        $material = \App\Models\Material::find($materialId);
        if (! $material) return;

        // If the material is part of an invoice, its debt is tracked at the invoice level
        if ($material->invoice_id) {
            $debt = SupplierDebt::where('invoice_id', $material->invoice_id)->first();
        } else {
            $debt = SupplierDebt::where('material_id', $materialId)->first();
        }

        if (! $debt) {
            return;
        }

        $newTotal = max((float) $debt->paid_amount, (float) $debt->total_amount + $delta);

        if ($newTotal <= 0.0 && (float) $debt->paid_amount == 0.0) {
            $debt->delete();
            return;
        }

        $newStatus = $newTotal <= (float) $debt->paid_amount + 0.009 ? 'paid' : ((float) $debt->paid_amount > 0 ? 'partial' : 'pending');

        $debt->update([
            'total_amount' => $newTotal,
            'status'       => $newStatus,
        ]);
    }
}
