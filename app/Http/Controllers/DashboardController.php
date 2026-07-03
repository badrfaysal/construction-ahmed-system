<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Installment;
use App\Models\Project;
use App\Models\Transaction;

class DashboardController extends Controller
{
    // Main dashboard — summary stats and active project cards
    public function index()
    {
        // Eager load what we need to avoid N+1 queries in the view
        $projects = Project::with(['client', 'bands.materials.returns', 'materials.returns', 'installments'])->latest()->get();

        $activeProjects = $projects->where('status', 'active');
        $doneProjects   = $projects->where('status', 'done');

        // Total collected from clients across all projects
        $totalCollected = Installment::where('status', 'paid')->sum('amount');

        // Total contract value = sum of each project's locked-in initial value
        // (falls back to the live band-price sum for projects created before that column existed)
        $totalContract = $projects->sum(fn ($p) => $p->initialContractValue());

        // Overdue installments — past due date and not paid yet
        $overdueCount = Installment::where('status', 'due')
            ->where('due_date', '<', today())
            ->count();

        // Treasury balance: collected - spent (materials + labor)
        $totalSpentMaterials = $projects->sum(fn ($p) => $p->materials->sum(fn ($m) => $m->netCost()));

        $totalSpentLabor = \DB::table('sy2_project_bands')->sum('labor_amount');
        $treasuryBalance = $totalCollected - $totalSpentMaterials - $totalSpentLabor;

        // Real wallet balance — محفظة المقاولات — the actual account every
        // expense/income in the system debits/credits (see TransactionObserver)
        $walletBalance = Account::walletBalance();

        // Last 5 transactions for the quick feed on dashboard
        $recentTransactions = Transaction::with('project')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'projects', 'activeProjects', 'doneProjects',
            'totalCollected', 'totalContract', 'overdueCount',
            'treasuryBalance', 'totalSpentMaterials', 'totalSpentLabor',
            'walletBalance', 'recentTransactions'
        ));
    }
}
