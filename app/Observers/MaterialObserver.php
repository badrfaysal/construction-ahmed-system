<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\SupplierDebt;
use App\Models\Transaction;

// Keeps سجل الحركات (transactions log) in sync with material purchases automatically.
// Also creates/adjusts supplier-debt records when a purchase is partial or deferred.
class MaterialObserver
{
    public function created(Material $material): void
    {
        $walletAmount = $this->walletAmount($material);

        // Only create a transaction (wallet debit) when something is actually paid now
        if ($walletAmount > 0) {
            Transaction::create([
                'project_id'  => $material->project_id,
                'band_id'     => $material->band_id,
                'direction'   => 'out',
                'type'        => 'شراء مواد',
                'party'       => $material->supplier?->name ?? $material->item,
                'amount'      => $walletAmount,
                'date'        => $material->date,
                'description' => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                'ref_type'    => 'material',
                'ref_id'      => $material->id,
            ]);
        }

        // Record debt for unpaid portion (partial or deferred)
        $debtAmount = $material->netCost() - $walletAmount;
        if ($debtAmount > 0) {
            SupplierDebt::create([
                'project_id'   => $material->project_id,
                'band_id'      => $material->band_id,
                'supplier_id'  => $material->supplier_id,
                'material_id'  => $material->id,
                'description'  => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                'total_amount' => $debtAmount,
                'paid_amount'  => 0,
                'status'       => 'pending',
            ]);
        }
    }

    public function updated(Material $material): void
    {
        $walletAmount = $this->walletAmount($material);

        $tx = Transaction::where('ref_type', 'material')->where('ref_id', $material->id)->first();

        if ($walletAmount > 0) {
            if ($tx) {
                $tx->update([
                    'project_id'  => $material->project_id,
                    'band_id'     => $material->band_id,
                    'party'       => $material->supplier?->name ?? $material->item,
                    'amount'      => $walletAmount,
                    'date'        => $material->date,
                    'description' => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                ]);
            } else {
                Transaction::create([
                    'project_id'  => $material->project_id,
                    'band_id'     => $material->band_id,
                    'direction'   => 'out',
                    'type'        => 'شراء مواد',
                    'party'       => $material->supplier?->name ?? $material->item,
                    'amount'      => $walletAmount,
                    'date'        => $material->date,
                    'description' => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                    'ref_type'    => 'material',
                    'ref_id'      => $material->id,
                ]);
            }
        } elseif ($tx) {
            // Was paid before, now deferred — remove transaction
            $tx->delete();
        }

        // Sync debt: only update if the debt originated from this material
        $debt = SupplierDebt::where('material_id', $material->id)->first();
        $debtAmount = $material->netCost() - $walletAmount;

        if ($debtAmount > 0) {
            if ($debt) {
                $debt->update([
                    'project_id'   => $material->project_id,
                    'band_id'      => $material->band_id,
                    'supplier_id'  => $material->supplier_id,
                    'description'  => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                    'total_amount' => $debtAmount,
                ]);
            } else {
                SupplierDebt::create([
                    'project_id'   => $material->project_id,
                    'band_id'      => $material->band_id,
                    'supplier_id'  => $material->supplier_id,
                    'material_id'  => $material->id,
                    'description'  => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
                    'total_amount' => $debtAmount,
                    'paid_amount'  => 0,
                    'status'       => 'pending',
                ]);
            }
        } elseif ($debt && $debt->paid_amount == 0) {
            // Debt is fully cancelled (purchase is now fully paid), remove it
            $debt->delete();
        }
    }

    public function deleted(Material $material): void
    {
        // Removing the transaction credits the wallet back
        Transaction::where('ref_type', 'material')->where('ref_id', $material->id)->first()?->delete();
        // Remove any outstanding debt linked to this purchase
        SupplierDebt::where('material_id', $material->id)->where('paid_amount', 0)->delete();
    }

    // How much hits the wallet right now based on payment_status
    private function walletAmount(Material $material): float
    {
        return match ($material->payment_status ?? 'paid') {
            'partial'  => (float) $material->paid_amount,
            'deferred' => 0.0,
            default    => $material->netCost(), // 'paid'
        };
    }
}
