<?php

namespace App\Services;

use App\Models\Account;
use App\Models\InstallmentContract;
use App\Models\Project;
use App\Models\SupplierDebt;
use Illuminate\Support\Facades\DB;

class CapitalService
{
    /**
     * Calculate current net capital and return it with breakdown details.
     * This always calculates the absolute current totals, without any date filters.
     *
     * @return array
     */
    public static function calculateCurrentCapital(): array
    {
        $allProjects = Project::with(['client', 'contracts', 'bands', 'discounts'])
            ->withSum(['transactions as total_worker_paid' => function ($query) {
                $query->where('ref_type', 'worker_payment');
            }], 'amount')
            ->get();

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
            if ($p->hasInstallmentContract()) {
                return 0;
            }
            return max(0, $p->cached_collected + $p->cached_discount - $p->cached_actual_total);
        });

        $supplierDebtsRemaining = (float) (SupplierDebt::where('sy2_supplier_debts.status', '!=', 'paid')
            ->join('sy2_projects', 'sy2_supplier_debts.project_id', '=', 'sy2_projects.id')
            ->selectRaw('SUM(sy2_supplier_debts.total_amount - sy2_supplier_debts.paid_amount) as r')
            ->value('r') ?? 0);

        $totalWorkerBase = (float) DB::table('sy2_band_workers')
            ->join('sy2_project_bands', 'sy2_band_workers.project_band_id', '=', 'sy2_project_bands.id')
            ->join('sy2_projects', 'sy2_project_bands.project_id', '=', 'sy2_projects.id')
            ->sum('sy2_band_workers.amount');

        $totalWorkerDeferred = (float) DB::table('sy2_materials')
            ->join('sy2_projects', 'sy2_materials.project_id', '=', 'sy2_projects.id')
            ->whereNotNull('sy2_materials.band_worker_id')
            ->where('sy2_materials.category', 'misc')
            ->where('sy2_materials.payment_status', 'deferred')
            ->sum(DB::raw('sy2_materials.qty * sy2_materials.unit_price'));

        $totalWorkerContracted = $totalWorkerBase + $totalWorkerDeferred;

        $totalWorkerPaidAndDiscount = (float) DB::table('sy2_worker_payments')
            ->join('sy2_projects', 'sy2_worker_payments.project_id', '=', 'sy2_projects.id')
            ->sum(DB::raw('sy2_worker_payments.amount + sy2_worker_payments.discount'));

        $unpaidLabor = max($totalWorkerContracted - $totalWorkerPaidAndDiscount, 0);

        $manualDebtsDue = (float) \App\Models\ManualDebt::where('status', '!=', 'paid')
            ->where('type', 'debt')
            ->sum(DB::raw('total_amount - paid_amount'));

        $manualReceivables = (float) \App\Models\ManualDebt::where('status', '!=', 'paid')
            ->where('type', 'receivable')
            ->sum(DB::raw('total_amount - paid_amount'));

        $directReceivables += $manualReceivables;
        $supplierDebtsRemaining += $manualDebtsDue;

        $netCapital = $accountsBalance + $directReceivables + $installmentReceivables - $supplierDebtsRemaining - $unpaidLabor - $clientOverpayments;

        $accountsBreakdown = Account::where('status', 'active')
            ->get(['id', 'name', 'balance'])
            ->map(fn($acc) => [
                'id' => $acc->id,
                'name' => $acc->name,
                'balance' => (float) $acc->balance
            ])->toArray();

        return [
            'net_capital' => $netCapital,
            'details' => [
                'accountsBalance' => $accountsBalance,
                'accounts_breakdown' => $accountsBreakdown,
                'directReceivables' => $directReceivables,
                'installmentReceivables' => $installmentReceivables,
                'supplierDebtsRemaining' => $supplierDebtsRemaining,
                'unpaidLabor' => $unpaidLabor,
                'clientOverpayments' => $clientOverpayments,
                'manualDebtsDue' => $manualDebtsDue,
                'manualReceivables' => $manualReceivables,
            ]
        ];
    }
}
