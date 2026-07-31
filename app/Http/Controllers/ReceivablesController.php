<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Installment;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReceivablesController extends Controller
{
    // Shows what clients owe us — split into per-project receivables and overdue installments
    public function index(Request $request)
    {
        $query = Project::with([
            'client',
            'contracts', // Needed for hasInstallmentContract()
            'clientPayments' // Needed for receivableExcess()
        ])->orderByDesc('created_at');

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $projects = $query->get();

        $rows = $projects->map(function ($project) {
            $billed    = $project->grossClientTotal();
            $collected = $project->totalCollected();
            $discount  = $project->totalDiscount();

            // Exclude installment contract numbers to completely separate them from receivables
            if ($project->hasInstallmentContract()) {
                $contractScope = $project->contractedScope();
                $contractCollected = (float) $project->contracts->sum(fn ($c) => (float) $c->payments->sum('amount_paid'));
                $contractDiscount = (float) $project->contracts->sum('discount');

                $billed    = max(0, $billed - $contractScope);
                $collected = max(0, $collected - $contractCollected);
                $discount  = max(0, $discount - $contractDiscount);
            }

            $remaining = max(0, $billed - $collected - $discount);

            return (object) [
                'project'       => $project,
                'billed'        => $billed,
                'collected'     => $collected,
                'discount'      => $discount,
                'remaining'     => $remaining,
                'excess'        => $project->hasInstallmentContract() ? $remaining : null,
                'book_profit'   => max(0, $billed - $project->computeTotalCost()),
                'earned_profit' => max(0, $collected - $project->totalSpent()),
            ];
        })->filter(fn ($r) => $r->billed > 0 || $r->remaining > 0);

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'amount_desc') {
            $rows = $rows->sortByDesc('remaining');
        } elseif ($sort === 'amount_asc') {
            $rows = $rows->sortBy('remaining');
        } else {
            // newest
            $rows = $rows->sortByDesc(fn($r) => $r->project->created_at);
        }

        // Overdue installments across all projects
        $overdueInstallments = Installment::with(['project.client', 'band'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        // Upcoming installments (next 60 days)
        $upcomingInstallments = Installment::with(['project.client', 'band'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', today()->addDays(60))
            ->orderBy('due_date')
            ->get();

        $manualReceivables = \App\Models\ManualDebt::where('type', 'receivable')->orderByDesc('date')->get();

        $manualTotals = [
            'total'     => $manualReceivables->sum('total_amount'),
            'collected' => $manualReceivables->sum('paid_amount'),
            'remaining' => $manualReceivables->sum(fn($r) => $r->total_amount - $r->paid_amount),
        ];

        $totals = [
            'total_billed'    => $rows->sum('billed'),
            'total_collected' => $rows->sum('collected'),
            'total_remaining' => $rows->sum('remaining'),
            'book_profit'     => $rows->sum('book_profit'),
            'earned_profit'   => $rows->sum('earned_profit'),
        ];

        $wallets = Account::selectable();

        return view('receivables.index', compact('rows', 'overdueInstallments', 'upcomingInstallments', 'totals', 'wallets', 'manualReceivables', 'manualTotals'));
    }

    public function payManual(Request $request, \App\Models\ManualDebt $receivable)
    {
        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0.01', 'max:' . $receivable->remaining()],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        DB::transaction(function () use ($receivable, $data) {
            $newPaid = (float) $receivable->paid_amount + (float) $data['amount'];
            $newStatus = $newPaid >= (float) $receivable->total_amount ? 'paid' : 'partial';

            $receivable->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
            ]);

            Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $data['account_id'],
                'direction'   => 'in',
                'type'        => 'تحصيل مستحق يدوي',
                'party'       => $receivable->party,
                'amount'      => (float) $data['amount'],
                'date'        => $data['pay_date'],
                'description' => 'تحصيل سلفة/مستحق من: ' . $receivable->party,
                'ref_type'    => 'manual_receivable',
                'ref_id'      => $receivable->id,
            ]);
        });

        return back()->with('success', 'تم تسجيل التحصيل بنجاح.');
    }

    // تسجيل تحصيل مباشر من العميل على مشروع (دفعة جزئية أو سداد كامل للمتبقي).
    // لو المشروع معموله عقد تقسيط، عقد التقسيط بيفضل شغال لوحده (يتحصّل من
    // صفحة الأقساط) — لكن أي فوترة جديدة حصلت بعد العقد (خامة/بند إضافي) بتبقى
    // مستحق عادي قابل للتحصيل من هنا، لحد سقف المستحق الزيادة ده (receivableExcess).
    // كل تحصيل حركة "in" بتغذّي المحفظة عبر TransactionObserver.
    public function pay(Request $request, Project $project)
    {
        $project->load([
            'client', 'contracts.payments', 'clientPayments',
            'bands.materials.returns', 'bands.workers', 'materials.returns',
        ]);

        $hasContract = $project->hasInstallmentContract();
        $remaining   = $hasContract ? $project->receivableExcess() : $project->amountDue();

        if ($hasContract && $remaining <= 0.009) {
            return back()->with('error', 'مفيش مستحق إضافي خارج نطاق عقد التقسيط — التحصيل على العقد نفسه بيتم من صفحة الأقساط.')
                ->with('reopen_project', $project->id);
        }

        // بنستخدم Validator يدويًا (بدل $request->validate() اللي بيرمي فورًا)
        // عشان نقدر نلحق 'reopen_project' بالـ redirect ونفتح مودال المشروع ده
        // تاني تلقائيًا لو الفاليديشن فشلت — من غيرها المودال بيتقفل والمستخدم
        // مش هيشوف مكان الخطأ ولا يفقد اللي كتبه.
        $validator = Validator::make($request->all(), [
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'band_id'    => ['nullable', 'integer', 'exists:sy2_project_bands,id'],
            'date'       => ['required', 'date'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('reopen_project', $project->id);
        }

        $data = $validator->validated();
        $discount = (float) ($data['discount'] ?? 0);

        // البند لازم يكون تابع لنفس المشروع (لو اتحدد بند معيّن)
        if (! empty($data['band_id']) && ! $project->bands()->whereKey($data['band_id'])->exists()) {
            return back()->with('error', 'البند المختار مش تابع لهذا المشروع.')->with('reopen_project', $project->id);
        }

        $warning = null;
        if ($data['amount'] + $discount > $remaining + 0.01) {
            $label = $hasContract ? 'المستحق الإضافي خارج عقد التقسيط' : 'المتبقي على العميل';
            $warning = 'تم التسجيل بنجاح، لكن انتبه: المجموع (تحصيل + خصم) أكبر من ' . $label . ' (دفعة تحت الحساب).';
        }

        // وصف الدفعة: عامة للمشروع أو تحت بند محدد
        $bandName = ! empty($data['band_id'])
            ? optional($project->bands()->whereKey($data['band_id'])->first())->name
            : null;
        $desc = ($hasContract ? 'تحصيل مستحق إضافي خارج عقد التقسيط' : 'تحصيل مباشر من المستحقات')
            . ($bandName ? ' — بند: ' . $bandName : ' — دفعة عامة للمشروع')
            . (! empty($data['notes']) ? ' — ' . $data['notes'] : '');

        DB::transaction(fn () => Transaction::create([
            'project_id'  => $project->id,
            'band_id'     => $data['band_id'] ?? null,
            'account_id'  => $data['account_id'],
            'direction'   => 'in',
            'type'        => 'تحصيل من العميل',
            'party'       => $project->client->name ?? null,
            'amount'      => $data['amount'],
            'discount'    => $discount,
            'date'        => $data['date'],
            'description' => $desc,
            'ref_type'    => 'client_payment',
            'ref_id'      => null,
        ]));

        if ($warning) {
            return back()->with('warning', $warning);
        }

        return back()->with('success', 'تم تسجيل تحصيل ' . number_format($data['amount']) . ' ج من العميل.');
    }

    public function payManualParty(Request $request)
    {
        $partyName = $request->input('party_name');
        
        $receivables = \App\Models\ManualDebt::where('type', 'receivable')
            ->where('party', $partyName)
            ->where('status', '!=', 'paid')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $totalRemaining = $receivables->sum(fn($r) => $r->remaining());

        if ($totalRemaining <= 0) {
            return back()->with('error', 'لا يوجد مستحقات معلقة لهذه الجهة/الشخص.');
        }

        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:0.01', 'max:' . $totalRemaining],
            'account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'pay_date'   => ['required', 'date'],
        ]);

        DB::transaction(function () use ($receivables, $data, $partyName) {
            $remainingPay = (float) $data['amount'];
            $transactions = [];
            
            foreach ($receivables as $recv) {
                if ($remainingPay <= 0) break;
                
                $recvRemaining = $recv->remaining();
                $pay = min($remainingPay, $recvRemaining);

                $newPaid = (float) $recv->paid_amount + $pay;
                $newStatus = $newPaid >= (float) $recv->total_amount ? 'paid' : 'partial';
                
                $recv->update([
                    'paid_amount' => $newPaid,
                    'status'      => $newStatus,
                ]);

                $transactions[] = [
                    'project_id'  => $recv->project_id,
                    'band_id'     => $recv->band_id,
                    'account_id'  => $data['account_id'],
                    'direction'   => 'in',
                    'type'        => 'تحصيل سلفة/مستحق يدوي',
                    'party'       => $partyName,
                    'amount'      => $pay,
                    'date'        => $data['pay_date'],
                    'description' => 'تحصيل: ' . $recv->description,
                    'ref_type'    => 'manual_recv',
                    'ref_id'      => $recv->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $remainingPay -= $pay;
            }

            if (!empty($transactions)) {
                Transaction::insert($transactions);
            }
        });

        return back()->with('success', "تم تحصيل مبلغ " . number_format($data['amount'], 2) . " ج.م من " . $partyName . " بنجاح.");
    }
}
