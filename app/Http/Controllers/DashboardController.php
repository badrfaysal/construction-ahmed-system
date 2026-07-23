<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\InstallmentContract;
use App\Models\Project;
use App\Models\SupplierDebt;
use App\Models\Transaction;

class DashboardController extends Controller
{
    // Main dashboard — summary stats and active project cards
    public function index(\Illuminate\Http\Request $request)
    {
        $monthFilter = $request->input('month', date('Y-m'));
        $isFiltered = $monthFilter !== 'all';
        $startDate = $isFiltered ? \Carbon\Carbon::parse($monthFilter . '-01')->startOfMonth() : null;
        $endDate = $isFiltered ? \Carbon\Carbon::parse($monthFilter . '-01')->endOfMonth() : null;

        // Use cached totals to avoid massive eager loading
        $allProjects = Project::with(['client', 'contracts', 'bands', 'discounts'])
            ->withSum(['transactions as total_worker_paid' => function ($query) {
                $query->where('ref_type', 'worker_payment');
            }], 'amount')
            ->latest()
            ->get();

        // Projects filtered by the selected month
        $projects = $isFiltered 
            ? $allProjects->filter(fn($p) => $p->created_at >= $startDate && $p->created_at <= $endDate)
            : $allProjects;

        $activeProjects    = $projects->where('status', 'active');
        $doneProjects      = $projects->where('status', 'done');
        $suspendedProjects = $projects->where('status', 'suspended');
        $canceledProjects  = $projects->where('status', 'canceled');

        // Total collected from clients = down payments + all installment payments
        if ($isFiltered) {
            $totalCollected = (float) InstallmentContract::whereBetween('start_date', [$startDate, $endDate])->sum('down_payment')
                + (float) \DB::table('sy2_installment_payments')->whereBetween('payment_date', [$startDate, $endDate])->sum('amount_paid');
        } else {
            $totalCollected = (float) InstallmentContract::sum('down_payment')
                + (float) \DB::table('sy2_installment_payments')->sum('amount_paid');
        }

        // Total contract value = sum of each project's locked-in initial value
        $totalContract = $projects->sum(fn ($p) => $p->initialContractValue());

        // Overdue contracts
        $overdueCount = InstallmentContract::with('payments')
            ->where('remaining_balance', '>', 0)
            ->where('due_day', '<', (int) date('d'))
            ->get()
            ->filter(fn ($c) => ! $c->isPaidThisMonth())
            ->count();

        // Total due from installment contracts
        $installmentContractsDue = (float) InstallmentContract::sum('remaining_balance');

        // Treasury balance: collected - spent (materials + labor)
        $totalSpentLabor = \DB::table('sy2_project_bands')->sum('labor_amount');
        $totalSpentAll   = $projects->sum('cached_spent');
        // Total spent on marketers
        $totalSpentMarketers = \DB::table('sy2_transactions')->where('ref_type', 'marketer_commission')->sum('amount');
        // Derive materials
        $totalSpentMaterials = max(0, $totalSpentAll - $totalSpentLabor - $totalSpentMarketers);
        
        $treasuryBalance = $totalCollected - $totalSpentMaterials - $totalSpentLabor;

        // صافي كل الحركات المالية المرتبطة بمشاريع عبر كل الحسابات
        // (بدل رصيد محفظة المقاولات فقط — عشان يشمل أي حساب بنكي أو محفظة)
        $constructionNetCash = FinancialTransaction::constructionNetCash();

        // رصيد المحفظة الافتراضية (للعرض فقط في الكارت المنفصل)
        $walletBalance = Account::walletBalance();

        // Capital metrics use ALL projects (excluding from month filter as requested)
        $directReceivables = (float) $allProjects
            ->reject(fn ($p) => $p->hasInstallmentContract())
            ->sum(fn ($p) => max(0, $p->cached_actual_total - $p->cached_collected));

        $installmentReceivables = (float) $allProjects
            ->filter(fn ($p) => $p->hasInstallmentContract())
            ->sum(fn ($p) => max(0, $p->cached_actual_total - $p->cached_collected));

        $supplierDebtsRemaining = (float) (SupplierDebt::where('status', '!=', 'paid')
            ->selectRaw('SUM(total_amount - paid_amount) as r')
            ->value('r') ?? 0);

        $totalWorkerContracted = (float) \DB::table('sy2_band_workers')->sum('amount');
        $totalWorkerPaidAndDiscount = (float) \DB::table('sy2_worker_payments')->sum(\DB::raw('amount + discount'));
        $unpaidLabor = max($totalWorkerContracted - $totalWorkerPaidAndDiscount, 0);

        $netCapital = $constructionNetCash + $directReceivables + $installmentReceivables - $supplierDebtsRemaining - $unpaidLabor;

        // Last 5 transactions for the quick feed on dashboard
        $recentTransactionsQuery = Transaction::with('project')->orderByDesc('date')->orderByDesc('id');
        if ($isFiltered) {
            $recentTransactionsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $recentTransactions = $recentTransactionsQuery->limit(5)->get();

        return view('dashboard.index', compact(
            'projects', 'activeProjects', 'doneProjects', 'suspendedProjects', 'canceledProjects',
            'totalCollected', 'totalContract', 'overdueCount', 'installmentContractsDue',
            'treasuryBalance', 'totalSpentMaterials', 'totalSpentLabor',
            'walletBalance', 'constructionNetCash', 'recentTransactions',
            'directReceivables', 'installmentReceivables', 'supplierDebtsRemaining', 'unpaidLabor', 'netCapital',
            'monthFilter', 'isFiltered'
        ));
    }
}
