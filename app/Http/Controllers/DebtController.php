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

        match ($request->get('sort', 'due_asc')) {
            'newest'      => $query->orderByDesc('created_at'),
            'amount_desc' => $query->orderByDesc('total_amount'),
            'amount_asc'  => $query->orderBy('total_amount'),
            default       => $query->orderBy('due_date'),
        };

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        if ($sid = $request->get('supplier_id')) {
            $query->where('supplier_id', $sid);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            // Default: hide fully-paid debts
            $query->where('status', '!=', 'paid');
        }

        $debts     = $query->paginate(60);
        $projects  = Project::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $wallets   = Account::selectable();

        // Summary totals (unfiltered except for project if selected)
        $baseQuery = SupplierDebt::query();
        if ($pid = $request->get('project_id')) {
            $baseQuery->where('project_id', $pid);
        }
        $totals = [
            'total_debt'     => (float) $baseQuery->clone()->where('status', '!=', 'paid')->sum('total_amount'),
            'paid_so_far'    => (float) $baseQuery->clone()->where('status', '!=', 'paid')->sum('paid_amount'),
            'remaining'      => (float) $baseQuery->clone()->where('status', '!=', 'paid')->selectRaw('SUM(total_amount - paid_amount) as r')->value('r'),
            'overdue_count'  => $baseQuery->clone()->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', today())->count(),
        ];

        return view('debts.index', compact('debts', 'projects', 'suppliers', 'wallets', 'totals'));
    }

    // Partially or fully pay off a debt
    public function pay(Request $request, SupplierDebt $debt)
    {
        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0.01', 'max:' . $debt->remaining()],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        DB::transaction(function () use ($debt, $data) {
            $newPaid = (float) $debt->paid_amount + (float) $data['amount'];
            $newStatus = $newPaid >= (float) $debt->total_amount ? 'paid' : 'partial';

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
                'amount'      => (float) $data['amount'],
                'date'        => $data['pay_date'],
                'description' => 'سداد: ' . $debt->description,
                'ref_type'    => 'debt',
                'ref_id'      => $debt->id,
            ]);
        });

        return back()->with('success', 'تم تسجيل الدفع.');
    }

    // Delete a debt (admin use — e.g. data entry error)
    public function destroy(SupplierDebt $debt)
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);
        $debt->delete();
        return back()->with('success', 'تم حذف الدين.');
    }
}
