<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

        // Total due from installment contracts
        $installmentContractsDue = (float) InstallmentContract::sum('remaining_balance');

        $capitalData = \App\Services\CapitalService::calculateCurrentCapital();
        $netCapital = $capitalData['net_capital'];
        
        $accountsBalance = $capitalData['details']['accountsBalance'];
        $directReceivables = $capitalData['details']['directReceivables'];
        $installmentReceivables = $capitalData['details']['installmentReceivables'];
        $supplierDebtsRemaining = $capitalData['details']['supplierDebtsRemaining'];
        $unpaidLabor = $capitalData['details']['unpaidLabor'];
        $clientOverpayments = $capitalData['details']['clientOverpayments'];

        // Fetch all accounts
        $accounts = Account::where('status', 'active')->orderBy('id')->get();

        // Last 5 transactions for the quick feed on dashboard
        $recentTransactionsQuery = Transaction::with('project')->orderByDesc('date')->orderByDesc('id');
        if ($isFiltered) {
            $recentTransactionsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $recentTransactions = $recentTransactionsQuery->limit(5)->get();

        // Profits Calculations
        $totalBilled = $projects->sum(fn($p) => $p->grossClientTotal());
        $totalSpent  = $projects->sum(fn($p) => $p->totalSpent());
        $totalCollectedFromProjects = $projects->sum(fn($p) => $p->totalCollected());
        $totalDiscount = $projects->sum(fn($p) => $p->totalDiscount());
        
        $totalTradeProfit       = $projects->sum(fn ($p) => $p->tradeProfit());
        $totalPercentageProfit  = $projects->sum(fn ($p) => $p->percentageProfit());
        $totalInstallmentProfit = $projects->sum(fn ($p) => $p->totalInstallmentInterest());

        $totalRevenuesForView = $totalTradeProfit + $totalPercentageProfit + $totalInstallmentProfit;
        
        // الربح الدفتري = إجمالي الأرباح (الإيرادات) - الخصومات
        $bookProfit = $totalRevenuesForView - $totalDiscount;
        // الربح الحقيقي = ما تم تحصيله فعلاً - ما تم صرفه فعلاً
        $realProfit = $totalCollectedFromProjects - $totalSpent;
        $uncollectedProfit = $bookProfit - $realProfit;

        $totalMarketerCommissions = (float) \App\Models\Transaction::where('ref_type', 'marketer_commission')->sum('amount');
        $totalReturnLosses = (float) \App\Models\MaterialReturn::with('material')->get()->sum(fn($r) => $r->loss());
        $totalDiscountsAndLosses = $totalDiscount + $totalMarketerCommissions + $totalReturnLosses;
        
        $totalGeneralExpensesQuery = \App\Models\Expense::whereNull('project_id');
        if ($isFiltered) {
            $totalGeneralExpensesQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $totalGeneralExpenses = (float) $totalGeneralExpensesQuery->sum('amount');

        // Fetch snapshots for the last 30 days for the chart
        $capitalSnapshots = \App\Models\CapitalSnapshot::where('snapshot_date', '>=', now()->subDays(30))
            ->orderBy('snapshot_date', 'asc')
            ->get(['snapshot_date', 'net_capital']);

        return view('dashboard.index', compact(
            'activeProjects', 'installmentContractsDue',
            'accountsBalance', 'directReceivables', 'installmentReceivables', 'supplierDebtsRemaining', 'unpaidLabor', 'clientOverpayments', 'netCapital',
            'monthFilter', 'isFiltered', 'accounts', 'recentTransactions',
            'bookProfit', 'realProfit', 'uncollectedProfit', 'totalDiscount',
            'totalInstallmentProfit', 'totalTradeProfit', 'totalPercentageProfit', 'totalRevenuesForView',
            'totalMarketerCommissions', 'totalReturnLosses', 'totalDiscountsAndLosses', 'capitalSnapshots', 'totalGeneralExpenses'
        ));
    }
}
