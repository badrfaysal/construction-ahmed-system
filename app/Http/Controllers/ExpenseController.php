<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::whereNull('project_id')->with('account');

        $range = $request->get('range');
        if ($range === 'today') {
            $query->whereDate('date', today());
        } elseif ($range === 'yesterday') {
            $query->whereDate('date', today()->subDay());
        } elseif ($range === 'week') {
            $query->whereBetween('date', [today()->subDays(6)->toDateString(), today()->toDateString()]);
        } elseif ($range === 'month') {
            $query->whereMonth('date', today()->month)->whereYear('date', today()->year);
        } elseif ($range === 'custom') {
            if ($request->from) $query->whereDate('date', '>=', $request->from);
            if ($request->to) $query->whereDate('date', '<=', $request->to);
        }

        $query->orderByDesc('date')->orderByDesc('id');

        $expenses = $query->paginate(30);
        
        // For stats, we need all the un-paginated data matching the filters
        $allExpenses = (clone $query)->get();

        $totalSpent = $allExpenses->sum('amount');
        
        $distribution = $allExpenses->groupBy('description')->map(fn($group) => $group->sum('amount'))->sortDesc();
        
        $mostSpentCat = $distribution->keys()->first();
        $mostSpentAmount = $distribution->first() ?? 0;
        
        $leastSpentCat = $distribution->keys()->last();
        $leastSpentAmount = $distribution->last() ?? 0;

        $uniqueDescriptions = Expense::whereNull('project_id')->select('description')->distinct()->pluck('description');
        $wallets = Account::selectable();

        return view('expenses.index', compact(
            'expenses', 'totalSpent', 'distribution', 'mostSpentCat', 'mostSpentAmount',
            'leastSpentCat', 'leastSpentAmount', 'uniqueDescriptions', 'wallets', 'range'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'account_id' => 'required|exists:sy2_accounts,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:sy2_projects,id',
        ]);

        DB::transaction(function () use ($data) {
            Expense::create($data);
        });

        if (!empty($data['project_id'])) {
            return back()->with('success', 'تم تسجيل المصروف على المشروع وخصم قيمته بنجاح.');
        }

        return back()->with('success', 'تم تسجيل المصروف بنجاح وخصم قيمته من الخزنة.');
    }

    public function destroy(Expense $expense)
    {
        DB::transaction(function () use ($expense) {
            $expense->delete();
        });

        return back()->with('success', 'تم مسح المصروف واسترجاع المبلغ للخزنة بنجاح.');
    }
}
