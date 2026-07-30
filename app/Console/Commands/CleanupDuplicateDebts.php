<?php

namespace App\Console\Commands;

use App\Models\SupplierDebt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateDebts extends Command
{
    protected $signature = 'debts:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Remove duplicate SupplierDebt records, keeping only the latest per invoice_id or material_id';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '🔍 DRY RUN — nothing will be deleted.' : '🧹 Cleaning up duplicate debts...');

        $deleted = 0;

        // 1. Duplicates by description (orphaned records from deleted invoices/materials)
        // Since the user deleted and recreated invoices, the old debts remained with different (or null) invoice_ids
        // but they share the exact same description.
        $descDupes = SupplierDebt::select('description', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('description')
            ->groupBy('description')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cnt', 'description');

        foreach ($descDupes as $desc => $count) {
            // Keep the latest row (highest id), delete the rest
            $latest = SupplierDebt::where('description', $desc)->orderByDesc('id')->first();
            $toDelete = SupplierDebt::where('description', $desc)
                ->where('id', '!=', $latest->id)
                ->get();

            foreach ($toDelete as $debt) {
                $this->line("  ❌ DELETE id={$debt->id} total={$debt->total_amount} paid={$debt->paid_amount} [{$debt->description}]");
                if (!$dryRun) {
                    $debt->delete();
                }
                $deleted++;
            }
            $this->line("  ✅ KEEP   id={$latest->id} total={$latest->total_amount} paid={$latest->paid_amount} [{$latest->description}]");
        }

        // 2. Debts where total_amount <= paid_amount but status is not 'paid' — fix status
        $wrongStatus = SupplierDebt::where('status', '!=', 'paid')
            ->whereRaw('paid_amount >= total_amount')
            ->where('total_amount', '>', 0)
            ->get();

        foreach ($wrongStatus as $debt) {
            $this->line("  🔧 FIX STATUS id={$debt->id} total={$debt->total_amount} paid={$debt->paid_amount} {$debt->status} → paid");
            if (!$dryRun) {
                $debt->update(['status' => 'paid']);
            }
        }

        // 4. Debts where total_amount is 0 or negative and nothing was paid — remove
        $zeroDebts = SupplierDebt::where('total_amount', '<=', 0)
            ->where('paid_amount', '<=', 0)
            ->get();

        foreach ($zeroDebts as $debt) {
            $this->line("  ❌ DELETE ZERO id={$debt->id} total={$debt->total_amount} [{$debt->description}]");
            if (!$dryRun) {
                $debt->delete();
            }
            $deleted++;
        }

        $this->info(PHP_EOL . "Done! " . ($dryRun ? "Would delete" : "Deleted") . " {$deleted} duplicate/invalid records.");
        $this->info("Fixed {$wrongStatus->count()} records with wrong status.");
        $this->info("Remaining records: " . SupplierDebt::count());

        return 0;
    }
}
