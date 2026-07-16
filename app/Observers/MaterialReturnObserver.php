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
    // Adding a return → refund the paid part to the wallet, drop the rest of
    // the debt. لو رجّعنا بسعر أقل من الشراء، الاسترداد والخصم من الدين
    // بيتحسبوا على سعر المرتجع الفعلي (مش سعر الشراء) — والفرق بيبقى خسارة
    // محقّقة (شايف MaterialReturn::loss()، بتتعرض في الواجهة بس مش حركة مالية).
    public function created(MaterialReturn $return): void
    {
        $material = $return->material()->with('supplier')->first();
        if (! $material) {
            return;
        }

        $paidRatio    = $material->paidRatio();
        $returnPrice  = $return->effectivePrice();
        $cashRefund = round($return->qty * $returnPrice * $paidRatio, 2);
        $debtDrop   = round($return->qty * $returnPrice * (1 - $paidRatio), 2);

        Transaction::create([
            'project_id'  => $material->project_id,
            'band_id'     => $material->band_id,
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
        Transaction::where('ref_type', 'return')->where('ref_id', $return->id)->first()?->delete();

        $material = $return->material()->first();
        if (! $material) {
            return;
        }

        $debtDrop = round($return->qty * $return->effectivePrice() * (1 - $material->paidRatio()), 2);
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
        $debt = SupplierDebt::where('material_id', $materialId)->first();
        if (! $debt) {
            return;
        }

        $newTotal = max((float) $debt->paid_amount, (float) $debt->total_amount + $delta);

        if ($newTotal <= 0.0 && (float) $debt->paid_amount == 0.0) {
            $debt->delete();
            return;
        }

        $debt->update(['total_amount' => $newTotal]);
    }
}
