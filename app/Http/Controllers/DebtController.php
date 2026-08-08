<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    // List all supplier debts — what we owe, grouped with filters
    public function index(Request $request)
    {
        $query = SupplierDebt::with(['project', 'band', 'supplier'])
            ->orderBy('status'); // pending first

        match ($request->get('sort', 'newest')) {
            'newest'      => $query->orderByDesc('created_at'),
            'amount_desc' => $query->orderByDesc('total_amount'),
            'amount_asc'  => $query->orderBy('total_amount'),
            'due_asc'     => $query->orderBy('due_date'),
            default       => $query->orderByDesc('created_at'),
        };

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        if ($sid = $request->get('supplier_id')) {
            $query->where('supplier_id', $sid);
        }

        // Remove backend status filter to allow frontend JS tabs to work
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $debts     = $query->get();
        $projects  = Project::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $wallets   = Account::selectable();

        // Summary totals (unfiltered except for project if selected)
        $baseQuery = SupplierDebt::query();
        if ($pid = $request->get('project_id')) {
            $baseQuery->where('project_id', $pid);
        }
        $manualTotalsQuery = \App\Models\ManualDebt::where('type', 'debt');
        $manualTotals = [
            'total_debt'  => (float) $manualTotalsQuery->clone()->where('status', '!=', 'paid')->sum('total_amount'),
            'paid_so_far' => (float) $manualTotalsQuery->clone()->where('status', '!=', 'paid')->sum('paid_amount'),
            'remaining'   => (float) $manualTotalsQuery->clone()->where('status', '!=', 'paid')->selectRaw('SUM(total_amount - paid_amount) as r')->value('r'),
        ];

        $totals = [
            'total_debt'     => (float) $baseQuery->clone()->where('status', '!=', 'paid')->sum('total_amount'),
            'paid_so_far'    => (float) $baseQuery->clone()->where('status', '!=', 'paid')->sum('paid_amount'),
            'remaining'      => (float) $baseQuery->clone()->where('status', '!=', 'paid')->selectRaw('SUM(total_amount - paid_amount) as r')->value('r'),
            'overdue_count'  => $baseQuery->clone()->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', today())->count(),
        ];

        $manualQuery = \App\Models\ManualDebt::where('type', 'debt')->orderByDesc('date');

        if ($status = $request->get('status')) {
            $manualQuery->where('status', $status);
        }

        if ($dateFrom = $request->get('date_from')) {
            $manualQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $manualQuery->whereDate('date', '<=', $dateTo);
        }

        $manualDebts = $manualQuery->get();

        // Earned discounts summary
        $earnedDiscounts = Transaction::whereIn('ref_type', ['debt', 'manual_debt'])
            ->where('discount', '>', 0)
            ->selectRaw('party, SUM(discount) as total_discount')
            ->groupBy('party')
            ->orderByDesc('total_discount')
            ->get();

        return view('debts.index', compact('debts', 'projects', 'suppliers', 'wallets', 'totals', 'manualDebts', 'manualTotals', 'earnedDiscounts'));
    }

    public function payManual(Request $request, \App\Models\ManualDebt $debt)
    {
        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        $cash = (float) $data['amount'];
        $discount = (float) ($data['discount'] ?? 0);
        $totalPay = $cash + $discount;

        if ($totalPay <= 0) {
            return back()->with('error', 'يجب إدخال مبلغ سداد أو تسوية.');
        }

        if (round($totalPay, 2) > round($debt->remaining(), 2)) {
            return back()->with('error', 'إجمالي السداد والتسوية أكبر من المتبقي من الدين.');
        }

        DB::transaction(function () use ($debt, $data, $cash, $discount, $totalPay) {
            $newPaid = (float) $debt->paid_amount + $totalPay;
            $newStatus = $newPaid >= (float) $debt->total_amount - 0.009 ? 'paid' : 'partial';

            $debt->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
            ]);

            Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $data['account_id'],
                'direction'   => 'out',
                'type'        => 'سداد دين يدوي',
                'party'       => $debt->party,
                'amount'      => $cash,
                'discount'    => $discount,
                'date'        => $data['pay_date'],
                'description' => 'سداد عهدة/دين لـ: ' . $debt->party,
                'ref_type'    => 'manual_debt',
                'ref_id'      => $debt->id,
            ]);
        });

        return back()->with('success', 'تم تسجيل سداد الدين بنجاح.');
    }

    // Partially or fully pay off a debt
    public function pay(Request $request, SupplierDebt $debt)
    {
        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        $cash = (float) $data['amount'];
        $discount = (float) ($data['discount'] ?? 0);
        $totalPay = $cash + $discount;

        if ($totalPay <= 0) {
            return back()->with('error', 'يجب إدخال مبلغ سداد أو تسوية.');
        }

        if (round($totalPay, 2) > round($debt->remaining(), 2)) {
            return back()->with('error', 'إجمالي السداد والتسوية أكبر من المتبقي من الدين.');
        }

        DB::transaction(function () use ($debt, $data, $cash, $discount, $totalPay) {
            $newPaid = (float) $debt->paid_amount + $totalPay;
            $newStatus = $newPaid >= (float) $debt->total_amount - 0.009 ? 'paid' : 'partial';

            $debt->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
            ]);

            // Debit wallet for the payment
            Transaction::create([
                'project_id'  => $debt->project_id,
                'band_id'     => $debt->band_id,
                'account_id'  => $data['account_id'],
                'direction'   => 'out',
                'type'        => 'سداد دين مورد',
                'party'       => $debt->supplier?->name ?? $debt->description,
                'amount'      => $cash,
                'discount'    => $discount,
                'date'        => $data['pay_date'],
                'description' => 'سداد: ' . $debt->description,
                'ref_type'    => 'debt',
                'ref_id'      => $debt->id,
            ]);
        });

        return back()->with('success', 'تم تسجيل الدفع.');
    }

    // Pay all/partial remaining debts for a specific supplier in one shot
    // Distributes the amount across unpaid/partial debts (oldest first).
    public function paySupplier(Request $request, int $supplierId)
    {
        $supplier = $supplierId > 0 ? Supplier::findOrFail($supplierId) : null;
        $debts = SupplierDebt::where('supplier_id', $supplierId > 0 ? $supplierId : null)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $totalRemaining = $debts->sum(fn($d) => $d->remaining());

        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        $cash = (float) $data['amount'];
        $discount = (float) ($data['discount'] ?? 0);
        $totalPay = $cash + $discount;

        if ($totalPay <= 0) {
            return back()->with('error', 'يجب إدخال مبلغ سداد أو تسوية.');
        }

        if (round($totalPay, 2) > round($totalRemaining, 2)) {
            return back()->with('error', 'إجمالي السداد والتسوية أكبر من المتبقي من الديون.');
        }

        DB::transaction(function () use ($debts, $data, $supplier, $cash, $discount) {
            $remainingCash = $cash;
            $remainingDiscount = $discount;

            foreach ($debts as $debt) {
                if ($remainingCash <= 0 && $remainingDiscount <= 0) break;
                
                $debtRemaining = $debt->remaining();
                $payTotal = min($remainingCash + $remainingDiscount, $debtRemaining);

                $payCash = min($remainingCash, $payTotal);
                $payDiscount = $payTotal - $payCash;

                $newPaid = (float) $debt->paid_amount + $payTotal;
                $newStatus = $newPaid >= (float) $debt->total_amount - 0.009 ? 'paid' : 'partial';
                $debt->update(['paid_amount' => $newPaid, 'status' => $newStatus]);

                Transaction::create([
                    'project_id'  => $debt->project_id,
                    'band_id'     => $debt->band_id,
                    'account_id'  => $data['account_id'],
                    'direction'   => 'out',
                    'type'        => 'سداد دين مورد',
                    'party'       => $supplier ? $supplier->name : 'بدون مورد',
                    'amount'      => $payCash,
                    'discount'    => $payDiscount,
                    'date'        => $data['pay_date'],
                    'description' => 'سداد: ' . $debt->description,
                    'ref_type'    => 'debt',
                    'ref_id'      => $debt->id,
                ]);

                $remainingCash -= $payCash;
                $remainingDiscount -= $payDiscount;
            }
        });
        $supplierName = $supplier ? $supplier->name : 'بدون مورد';
        return back()->with('success', 'تم تسجيل الدفع للمورد: ' . $supplierName . '.');
    }

    // Delete a debt (admin use — e.g. data entry error)
    public function destroy(SupplierDebt $debt)
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);
        $debt->delete();
        return back()->with('success', 'تم حذف الدين.');
    }

    public function payManualParty(Request $request)
    {
        $partyName = $request->input('party_name');
        
        $debts = \App\Models\ManualDebt::where('party', $partyName)
            ->where('status', '!=', 'paid')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $totalRemaining = $debts->sum(fn($d) => $d->remaining());

        if ($totalRemaining <= 0) {
            return back()->with('error', 'لا يوجد ديون معلقة لهذه الجهة/الشخص.');
        }

        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        $cash = (float) $data['amount'];
        $discount = (float) ($data['discount'] ?? 0);
        $totalPay = $cash + $discount;

        if ($totalPay <= 0) {
            return back()->with('error', 'يجب إدخال مبلغ سداد أو تسوية.');
        }

        if (round($totalPay, 2) > round($totalRemaining, 2)) {
            return back()->with('error', 'إجمالي السداد والتسوية أكبر من المتبقي من الديون.');
        }

        DB::transaction(function () use ($debts, $data, $partyName, $cash, $discount) {
            $remainingCash = $cash;
            $remainingDiscount = $discount;
            $transactions = [];
            
            foreach ($debts as $debt) {
                if ($remainingCash <= 0 && $remainingDiscount <= 0) break;
                
                $debtRemaining = $debt->remaining();
                $payTotal = min($remainingCash + $remainingDiscount, $debtRemaining);
                
                $payCash = min($remainingCash, $payTotal);
                $payDiscount = $payTotal - $payCash;

                $newPaid = (float) $debt->paid_amount + $payTotal;
                $newStatus = $newPaid >= (float) $debt->total_amount - 0.009 ? 'paid' : 'partial';
                
                $debt->update([
                    'paid_amount' => $newPaid,
                    'status'      => $newStatus,
                ]);

                $transactions[] = [
                    'project_id'  => $debt->project_id,
                    'band_id'     => $debt->band_id,
                    'account_id'  => $data['account_id'],
                    'direction'   => 'out',
                    'type'        => 'سداد عهدة/دين يدوي',
                    'party'       => $partyName,
                    'amount'      => $payCash,
                    'discount'    => $payDiscount,
                    'date'        => $data['pay_date'],
                    'description' => 'سداد: ' . $debt->description,
                    'ref_type'    => 'manual_debt',
                    'ref_id'      => $debt->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $remainingCash -= $payCash;
                $remainingDiscount -= $payDiscount;
            }

            if (!empty($transactions)) {
                foreach ($transactions as $txData) {
                    Transaction::create($txData);
                }
            }
        });

        return back()->with('success', "تم سداد مبلغ " . number_format($data['amount'], 2) . " ج.م لـ " . $partyName . " بنجاح.");
    }
}
