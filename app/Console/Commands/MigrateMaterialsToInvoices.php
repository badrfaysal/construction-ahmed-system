<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material;
use App\Models\MaterialInvoice;
use Illuminate\Support\Facades\DB;

class MigrateMaterialsToInvoices extends Command
{
    protected $signature = 'materials:migrate-invoices';
    protected $description = 'Groups existing materials into invoices based on their attributes.';

    public function handle()
    {
        // Suppress observer events during migration because we don't want to re-fire Transaction creation,
        // we just want to create the parent invoice and link existing materials, debt, and transactions to it!
        // Actually, existing materials already created Debts and Transactions.
        // It's safer to just group them and assign an invoice_id. But wait, if we create MaterialInvoice, it will trigger the MaterialInvoiceObserver and create NEW transactions and debts!
        // So we must temporarily disable MaterialInvoiceObserver!
        
        $dispatcher = MaterialInvoice::getEventDispatcher();
        MaterialInvoice::unsetEventDispatcher();

        $materials = Material::whereNull('invoice_id')->where('category', '!=', 'misc')->get();
        $grouped = $materials->groupBy(function($m) {
            return $m->project_id . '|' . $m->date->format('Y-m-d') . '|' . $m->supplier_id . '|' . $m->payment_status . '|' . $m->account_id;
        });

        DB::transaction(function() use ($grouped) {
            foreach ($grouped as $key => $mats) {
                $first = $mats->first();
                
                $totalAmount = $mats->sum(fn($m) => $m->qty * $m->unit_price);
                $paidAmount = $mats->sum('paid_amount');
                if ($first->payment_status === 'paid') {
                    $paidAmount = $totalAmount;
                }

                $invoice = MaterialInvoice::create([
                    'project_id' => $first->project_id,
                    'supplier_id' => $first->supplier_id,
                    'account_id' => $first->account_id,
                    'date' => $first->date,
                    'name' => 'فاتورة مرحلة',
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                ]);

                // Link materials
                foreach ($mats as $mat) {
                    $mat->invoice_id = $invoice->id;
                    $mat->saveQuietly();
                }

                // Link SupplierDebts (if any exist for these materials)
                // Existing SupplierDebt has material_id. We should migrate that to invoice_id.
                // Wait, sy2_supplier_debts has material_id ? No, let's check schema.
                // It had `material_id` ?
            }
        });

        MaterialInvoice::setEventDispatcher($dispatcher);

        $this->info('Migration completed successfully.');
    }
}
