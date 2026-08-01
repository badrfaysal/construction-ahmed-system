<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\Transaction;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        Transaction::create([
            'project_id'  => $expense->project_id,
            'account_id'  => $expense->account_id,
            'direction'   => 'out',
            'type'        => $expense->project_id ? 'مصروف مشروع' : 'مصروف عام',
            'party'       => 'مصروفات',
            'amount'      => $expense->amount,
            'date'        => $expense->date,
            'description' => $expense->description,
            'ref_type'    => $expense->project_id ? 'project_expense' : 'expense',
            'ref_id'      => $expense->id,
        ]);
    }

    public function deleted(Expense $expense): void
    {
        Transaction::whereIn('ref_type', ['expense', 'project_expense'])->where('ref_id', $expense->id)->first()?->delete();
    }
}
