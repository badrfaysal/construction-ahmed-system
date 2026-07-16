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

    public function deleted(MaterialInvoice $invoice): void
    {
        // Debt will be cascading deleted or manually deleted
        SupplierDebt::where('invoice_id', $invoice->id)->delete();
        Transaction::where('ref_type', 'material_invoice')->where('ref_id', $invoice->id)->delete();
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
        $debtAmount = $invoice->remainingBalance();
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
                $debt->delete();
            }
        }
    }
}
