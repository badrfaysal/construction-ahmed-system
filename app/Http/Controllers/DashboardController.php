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
    public function index()
    {
        // Eager load what we need to avoid N+1 queries in the view
        $projects = Project::with(['client', 'bands.materials.returns', 'materials.returns', 'contracts.payments', 'clientPayments'])->latest()->get();

        $activeProjects = $projects->where('status', 'active');
        $doneProjects   = $projects->where('status', 'done');

        // Total collected from clients = down payments + all installment payments
        $totalCollected = (float) InstallmentContract::sum('down_payment')
            + (float) \DB::table('sy2_installment_payments')->sum('amount_paid');

        // Total contract value = sum of each project's locked-in initial value
        // (falls back to the live band-price sum for projects created before that column existed)
        $totalContract = $projects->sum(fn ($p) => $p->initialContractValue());

        // Overdue contracts — active with a remaining balance whose monthly
        // installment for the current month hasn't been collected yet and the
        // due day already passed
        $overdueCount = InstallmentContract::with('payments')
            ->where('remaining_balance', '>', 0)
            ->where('due_day', '<', (int) date('d'))
            ->get()
            ->filter(fn ($c) => ! $c->isPaidThisMonth())
            ->count();

        // Treasury balance: collected - spent (materials + labor)
        $totalSpentMaterials = $projects->sum(fn ($p) => $p->materials->sum(fn ($m) => $m->netCost()));

        $totalSpentLabor = \DB::table('sy2_project_bands')->sum('labor_amount');
        $treasuryBalance = $totalCollected - $totalSpentMaterials - $totalSpentLabor;

        // Real wallet balance — محفظة المقاولات — the actual account every
        // expense/income in the system debits/credits (see TransactionObserver)
        $walletBalance = Account::walletBalance();

        // ── رأس مال مشروع المقاولات = السيولة (المحفظة) + كل المستحقات على
        // العملاء (مباشرة أو عبر عقود تقسيط) − الديون المتبقية للموردين.
        // نقسم المستحقات لسطرين (مباشر / عبر تقسيط) للعرض بس، من غير ما نحسب
        // نفس الفلوس مرتين: amountDue() لكل مشروع أصلاً بيطرح كل تحصيل اتم —
        // سواء عن طريق عقد أو تحصيل مباشر — فمفيش تداخل بين السطرين.
        $directReceivables = (float) $projects
            ->reject(fn ($p) => $p->hasInstallmentContract())
            ->sum(fn ($p) => max(0, $p->actualClientTotal() - $p->totalCollected()));

        $installmentReceivables = (float) $projects
            ->filter(fn ($p) => $p->hasInstallmentContract())
            ->sum(fn ($p) => max(0, $p->actualClientTotal() - $p->totalCollected()));

        $supplierDebtsRemaining = (float) (SupplierDebt::where('status', '!=', 'paid')
            ->selectRaw('SUM(total_amount - paid_amount) as r')
            ->value('r') ?? 0);

        $netCapital = $walletBalance + $directReceivables + $installmentReceivables - $supplierDebtsRemaining;

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
            'walletBalance', 'recentTransactions',
            'directReceivables', 'installmentReceivables', 'supplierDebtsRemaining', 'netCapital'
        ));
    }
}
