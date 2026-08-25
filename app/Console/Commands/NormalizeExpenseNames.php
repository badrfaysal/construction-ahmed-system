<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Support\ItemNameMatcher;

class NormalizeExpenseNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:normalize-expense-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize Arabic letters in existing expenses and categories (e.g. أ to ا, ة to ه)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting normalization...');

        $expenseCount = 0;
        foreach (Expense::all() as $e) {
            if (empty($e->description)) continue;
            
            $norm = ItemNameMatcher::normalizeLetters(trim($e->description));
            if ($e->description !== $norm) {
                $e->description = $norm;
                $e->saveQuietly();
                
                Transaction::whereIn('ref_type', ['expense', 'project_expense'])
                    ->where('ref_id', $e->id)
                    ->update(['description' => $norm]);
                    
                $expenseCount++;
            }
        }
        $this->info("Updated {$expenseCount} expenses and their corresponding transactions.");

        $catCount = 0;
        foreach (ExpenseCategory::all() as $cat) {
            if (empty($cat->name)) continue;
            
            $norm = ItemNameMatcher::normalizeLetters(trim($cat->name));
            if ($cat->name !== $norm) {
                try {
                    $cat->name = $norm;
                    $cat->saveQuietly();
                    $catCount++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // The normalized category already exists, so this one is redundant.
                    $cat->delete();
                }
            }
        }
        $this->info("Updated {$catCount} expense categories.");
        
        $this->info('Normalization completed successfully.');
    }
}
