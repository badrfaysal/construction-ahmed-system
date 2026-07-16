<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\SupplierDebt;
use App\Models\Transaction;

// Keeps سجل الحركات (transactions log) in sync with material purchases automatically.
// Also creates/adjusts supplier-debt records when a purchase is partial or deferred.
//
// The purchase's transaction is booked at the GROSS purchase amount and is
// never edited by returns — each return is booked as its own separate credit
// entry (see MaterialReturnObserver) so it shows up in سجل الحركات.
class MaterialObserver
{
    public function created(Material $material): void
    {
        if ($material->invoice_id) {
            $material->band?->recalculateCachedTotals();
            $material->project?->recalculateCachedTotals();
            return;
        }

        $walletAmount = $this->walletAmount($material);

        // Create a transaction so it always appears in the Radar/Audit Log.
        // If deferred, amount is 0, which correctly doesn't affect the wallet.
        Transaction::create([
            'project_id'  => $material->project_id,
            'band_id'     => $material->band_id,
            'account_id'  => $material->account_id,
            'direction'   => 'out',
            'type'        => 'شراء مواد',
            'party'       => $material->supplier?->name ?? $material->item,
            'amount'      => $walletAmount,
            'date'        => $material->date,
            'description' => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
            'ref_type'    => 'material',
            'ref_id'      => $material->id,
        ]);

        // Record debt for unpaid portion (partial or deferred). Based on the
        // gross purchase — a later return reduces this debt via
        // MaterialReturnObserver, so it must not already be netted here.
        $debtAmount = $material->grossCost() - $walletAmount;
        if ($debtAmount > 0) {
            SupplierDebt::create([
                'project_id'   => $material->project_id,
                'band_id'      => $material->band_id,
                'supplier_id'  => $material->supplier_id,
                'material_id'  => $material->id,
                'description'  => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
                'total_amount' => $debtAmount,
                'paid_amount'  => 0,
                'status'       => 'pending',
            ]);
        }

        $material->band?->recalculateCachedTotals();
        $material->project?->recalculateCachedTotals();
    }

    public function updated(Material $material): void
    {
        if ($material->invoice_id) {
            $material->band?->recalculateCachedTotals();
            $material->project?->recalculateCachedTotals();
            // Tell the invoice to update its totals
            $material->invoice->syncTotalAmount();
            return;
        }

        $walletAmount = $this->walletAmount($material);

        $tx = Transaction::where('ref_type', 'material')->where('ref_id', $material->id)->first();

        if ($tx) {
            $tx->update([
                'project_id'  => $material->project_id,
                'band_id'     => $material->band_id,
                'account_id'  => $material->account_id,
                'party'       => $material->supplier?->name ?? $material->item,
                'amount'      => $walletAmount,
                'date'        => $material->date,
                'description' => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
            ]);
        } else {
            Transaction::create([
                'project_id'  => $material->project_id,
                'band_id'     => $material->band_id,
                'account_id'  => $material->account_id,
                'direction'   => 'out',
                'type'        => 'شراء مواد',
                'party'       => $material->supplier?->name ?? $material->item,
                'amount'      => $walletAmount,
                'date'        => $material->date,
                'description' => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
                'ref_type'    => 'material',
                'ref_id'      => $material->id,
            ]);
        }

        // Sync debt: only update if the debt originated from this material
        $debt = SupplierDebt::where('material_id', $material->id)->first();
        $debtAmount = $material->grossCost() - $walletAmount;

        if ($debtAmount > 0) {
            if ($debt) {
                $debt->update([
                    'project_id'   => $material->project_id,
                    'band_id'      => $material->band_id,
                    'supplier_id'  => $material->supplier_id,
                    'description'  => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
                    'total_amount' => $debtAmount,
                ]);
            } else {
                SupplierDebt::create([
                    'project_id'   => $material->project_id,
                    'band_id'      => $material->band_id,
                    'supplier_id'  => $material->supplier_id,
                    'material_id'  => $material->id,
                    'description'  => $material->item . ' — ' . number_format($material->qty, 1) . ' ' . $material->unit,
                    'total_amount' => $debtAmount,
                    'paid_amount'  => 0,
                    'status'       => 'pending',
                ]);
            }
        } elseif ($debt && $debt->paid_amount == 0) {
            // Debt is fully cancelled (purchase is now fully paid), remove it
            $debt->delete();
        }

        $material->band?->recalculateCachedTotals();
        $material->project?->recalculateCachedTotals();
    }

    public function deleting(Material $material): void
    {
        // Remove any outstanding debt linked to this purchase before DB sets material_id to null
        SupplierDebt::where('material_id', $material->id)->where('paid_amount', 0)->delete();
    }

    public function deleted(Material $material): void
    {
        // Removing the transaction credits the wallet back
        Transaction::where('ref_type', 'material')->where('ref_id', $material->id)->first()?->delete();

        $material->band?->recalculateCachedTotals();
        $material->project?->recalculateCachedTotals();
    }

    // How much hits the wallet right now based on payment_status — the gross
    // paid cash (returns are credited back separately, not netted here)
    private function walletAmount(Material $material): float
    {
        return match ($material->payment_status ?? 'paid') {
            'partial'  => (float) $material->paid_amount,
            'deferred' => 0.0,
            default    => $material->grossCost(), // 'paid'
        };
    }
}
