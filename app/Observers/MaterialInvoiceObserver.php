<?php

namespace App\Observers;

use App\Models\MaterialInvoice;
use App\Models\SupplierDebt;
use App\Models\Transaction;

class MaterialInvoiceObserver
{
    public function created(MaterialInvoice $invoice): void
    {
        $this->syncFinancials($invoice);
    }

    public function updated(MaterialInvoice $invoice): void
    {
        $this->syncFinancials($invoice);
    }

    public function deleting(MaterialInvoice $invoice): void
    {
        // Reverse any manual payments made on this invoice's debts before deleting the debts
        $debts = SupplierDebt::where('invoice_id', $invoice->id)->get();
        foreach ($debts as $debt) {
            $txs = Transaction::where('ref_type', 'debt')->where('ref_id', $debt->id)->get();
            foreach ($txs as $tx) {
                $tx->delete(); // Refunds the wallet
            }
            $debt->delete();
        }
        
        Transaction::where('ref_type', 'material_invoice')->where('ref_id', $invoice->id)->first()?->delete();
    }

    private function syncFinancials(MaterialInvoice $invoice): void
    {
        $desc = 'فاتورة خامات';
        if ($invoice->name) {
            $desc .= ' - ' . $invoice->name;
        }

        // 1. Sync Transaction (Radar)
        // Only if paid_amount > 0 or if we want to show 0 amount transaction?
        // Let's show it even if 0, so the invoice shows in the radar.
        $tx = Transaction::where('ref_type', 'material_invoice')->where('ref_id', $invoice->id)->first();
        if ($tx) {
            $tx->update([
                'project_id'  => $invoice->project_id,
                'account_id'  => $invoice->account_id,
                'party'       => $invoice->supplier?->name ?? 'مورد عام',
                'amount'      => $invoice->paid_amount,
                'date'        => $invoice->date,
                'description' => $desc,
            ]);
        } else {
            Transaction::create([
                'project_id'  => $invoice->project_id,
                'account_id'  => $invoice->account_id,
                'direction'   => 'out',
                'type'        => 'شراء مواد',
                'party'       => $invoice->supplier?->name ?? 'مورد عام',
                'amount'      => $invoice->paid_amount,
                'date'        => $invoice->date,
                'description' => $desc,
                'ref_type'    => 'material_invoice',
                'ref_id'      => $invoice->id,
            ]);
        }

        // 2. Sync Supplier Debt
        // gross debt minus the total debt that was cancelled via returns
        $debtAmount = $invoice->remainingBalance() - $invoice->materials->sum(fn($m) => tap($m, fn() => $m->loadMissing('returns'))->returnsDebtDrop());
        $debt = SupplierDebt::where('invoice_id', $invoice->id)->first();

        if ($debtAmount > 0) {
            if ($debt) {
                $debt->update([
                    'project_id'   => $invoice->project_id,
                    'supplier_id'  => $invoice->supplier_id,
                    'description'  => $desc,
                    'total_amount' => $debtAmount,
                ]);
            } else {
                SupplierDebt::create([
                    'project_id'   => $invoice->project_id,
                    'supplier_id'  => $invoice->supplier_id,
                    'invoice_id'   => $invoice->id,
                    'description'  => $desc,
                    'total_amount' => $debtAmount,
                    'paid_amount'  => 0,
                    'status'       => 'pending',
                ]);
            }
        } else {
            // No debt remaining for this invoice
            if ($debt) {
                // Reverse any manual payments made on this debt before deleting it
                $txs = Transaction::where('ref_type', 'debt')->where('ref_id', $debt->id)->get();
                foreach ($txs as $tx) {
                    $tx->delete(); // Refunds the wallet
                }
                $debt->delete();
            }
        }
    }
}
