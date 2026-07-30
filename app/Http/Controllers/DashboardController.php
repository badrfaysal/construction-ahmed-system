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

        $accountsBalance = (float) Account::where('status', 'active')->sum('balance');

        $directReceivables = (float) $allProjects->sum(function ($p) {
            if ($p->hasInstallmentContract()) {
                return $p->receivableExcess();
            }
            return max(0, $p->cached_actual_total - $p->cached_collected - $p->cached_discount);
        });

        $installmentReceivables = $installmentContractsDue;

        $clientOverpayments = (float) $allProjects->sum(function ($p) {
            // Amount the client overpaid (collected + discount > billed)
            if ($p->hasInstallmentContract()) {
                return 0; // Overpayments on installments are handled differently or usually 0
            }
            return max(0, $p->cached_collected + $p->cached_discount - $p->cached_actual_total);
        });

        $supplierDebtsRemaining = (float) (SupplierDebt::where('sy2_supplier_debts.status', '!=', 'paid')
            ->join('sy2_projects', 'sy2_supplier_debts.project_id', '=', 'sy2_projects.id')
            ->selectRaw('SUM(sy2_supplier_debts.total_amount - sy2_supplier_debts.paid_amount) as r')
            ->value('r') ?? 0);

        $totalWorkerContracted = (float) \DB::table('sy2_band_workers')
            ->join('sy2_project_bands', 'sy2_band_workers.project_band_id', '=', 'sy2_project_bands.id')
            ->join('sy2_projects', 'sy2_project_bands.project_id', '=', 'sy2_projects.id')
            ->sum('sy2_band_workers.amount');

        $totalWorkerPaidAndDiscount = (float) \DB::table('sy2_worker_payments')
            ->join('sy2_projects', 'sy2_worker_payments.project_id', '=', 'sy2_projects.id')
            ->sum(\DB::raw('sy2_worker_payments.amount + sy2_worker_payments.discount'));

        $unpaidLabor = max($totalWorkerContracted - $totalWorkerPaidAndDiscount, 0);

        $netCapital = $accountsBalance + $directReceivables + $installmentReceivables - $supplierDebtsRemaining - $unpaidLabor - $clientOverpayments;

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

        return view('dashboard.index', compact(
            'activeProjects', 'installmentContractsDue',
            'accountsBalance', 'directReceivables', 'installmentReceivables', 'supplierDebtsRemaining', 'unpaidLabor', 'clientOverpayments', 'netCapital',
            'monthFilter', 'isFiltered', 'accounts', 'recentTransactions',
            'bookProfit', 'realProfit', 'uncollectedProfit', 'totalDiscount',
            'totalInstallmentProfit', 'totalTradeProfit', 'totalPercentageProfit', 'totalRevenuesForView',
            'totalMarketerCommissions', 'totalReturnLosses', 'totalDiscountsAndLosses'
        ));
    }
}
