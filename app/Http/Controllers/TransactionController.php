<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\Project;
use App\Models\SupplierDebt;
use App\Models\Transaction;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// سجل الحركات: القائمة نفسها بتتقرا من sy2_audit_logs (سجل تدقيق ثابت لا
// يُعدَّل ولا يُحذف) عشان تعرض كل حاجة حصلت فعلاً — إنشاء/تعديل/حذف — مش بس
// الحالة الحية النهائية اللي في sy2_transactions. الإجماليات (وارد/صادر) لسه
// بتتحسب من الحركات الحية (Transaction) عشان تفضل تعكس الرصيد الفعلي دلوقتي.
//
// تعديل/حذف الحركة الحية نفسها (update/destroy) مركزي هنا بس — الشاشات
// التانية (خامات، دفعات صنايعية، محفظة، مستحقات، أقساط، مرتجعات) بقت للعرض
// فقط. كلاهما أدمن فقط ومحمي بإعادة إدخال الباسورد.
class TransactionController extends Controller
{
    // Safe = تتعدّل/تتحذف مباشرة على الـ Transaction نفسها من غير ما تأثر
    // على موديل تاني. أي نوع تاني (owned) بيتحذف عن طريق الموديل المالك بتاعه
    // عشان أي تنظيف مرتبط (ديون، أرصدة، متبقي عقد...) يحصل صح.
    private const SAFE_REF_TYPES = ['manual', 'client_payment', null];

    public function index(Request $request)
    {
        $query = AuditLog::with(['project', 'band']);

        match ($request->get('sort', 'newest')) {
            'oldest'      => $query->orderBy('happened_at')->orderBy('id'),
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc'  => $query->orderBy('amount'),
            default       => $query->orderByDesc('happened_at')->orderByDesc('id'),
        };

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        if ($dir = $request->get('direction')) {
            $query->where('direction', $dir);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        $range = $request->get('range');
        match ($range) {
            'today'     => $query->whereDate('date', today()),
            'yesterday' => $query->whereDate('date', today()->subDay()),
            'week'      => $query->whereBetween('date', [today()->subDays(6)->toDateString(), today()->toDateString()]),
            'custom'    => $query
                ->when($request->get('from'), fn ($q) => $q->whereDate('date', '>=', $request->get('from')))
                ->when($request->get('to'), fn ($q) => $q->whereDate('date', '<=', $request->get('to'))),
            default => null,
        };

        $logs     = $query->paginate(30);
        $projects = Project::orderBy('name')->get(['id', 'name']);

        // مين لسه حي فعلاً دلوقتي — عشان زرار تعديل/حذف يبان بس على حركة
        // لسه موجودة (مش على أثر قديم اتلغى بالفعل)
        $liveIds = Transaction::pluck('id')->flip();

        // الإجماليات من الحركات الحيّة فقط (مش من كل أحداث السجل) عشان تعكس
        // الرصيد الفعلي، مع تطبيق نفس فلتر المشروع لو موجود
        $liveQuery = Transaction::query();
        if ($pid) {
            $liveQuery->where('project_id', $pid);
        }
        $totalIn  = (clone $liveQuery)->where('direction', 'in')->sum('amount');
        $totalOut = (clone $liveQuery)->where('direction', 'out')->sum('amount');

        $wallets = Account::selectable();

        return view('transactions.index', compact('logs', 'projects', 'totalIn', 'totalOut', 'liveIds', 'range', 'wallets'));
    }

    // تعديل مباشر — مسموح بس للحركات "الآمنة" (يدوية/تحصيل مباشر من عميل)
    // اللي مفيهاش موديل مالك تاني بيتأثر. محمي بإعادة إدخال باسورد الأدمن.
    public function update(Request $request, Transaction $transaction)
    {
        abort_unless(in_array($transaction->ref_type, self::SAFE_REF_TYPES, true), 403,
            'الحركة دي مرتبطة بسجل تاني (خامة/دفعة/دين...) — تتعدّلش مباشرة من هنا، احذفها وسجّلها تاني من مكانها الأصلي لو غلط.');

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'account_id'        => ['required', 'integer', 'exists:accounts,id'],
            'date'              => ['required', 'date'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ]);

        $this->verifyAdminPassword($data['current_password']);

        DB::transaction(fn () => $transaction->update([
            'amount'      => $data['amount'],
            'account_id'  => $data['account_id'],
            'date'        => $data['date'],
            'description' => $data['description'] ?? null,
        ]));

        return back()->with('success', 'تم تعديل الحركة بنجاح.');
    }

    // حذف حركة وعكس كل أثرها: لو "آمنة" بتتحذف مباشرة، لو مرتبطة بموديل
    // مالك (خامة/مرتجع/دفعة صنايعي/دفعة قسط/عقد) بيتحذف الموديل المالك نفسه
    // عشان أي تنظيف مرتبط (ديون، رصيد بند...) يحصل زي ما هو معمول له بالظبط.
    // الدين (SupplierDebt) خاص: مفيش صف مستقل لكل دفعة، فبنعكس المبلغ من
    // paid_amount بدل ما نحذف الدين كله. محمي بإعادة إدخال باسورد الأدمن.
    public function destroy(Request $request, Transaction $transaction)
    {
        $data = $request->validate(['current_password' => ['required', 'string']]);
        $this->verifyAdminPassword($data['current_password']);

        DB::transaction(function () use ($transaction) {
            switch ($transaction->ref_type) {
                case 'material':
                    Material::find($transaction->ref_id)?->delete();
                    break;
                case 'return':
                    MaterialReturn::find($transaction->ref_id)?->delete();
                    break;
                case 'worker_payment':
                    WorkerPayment::find($transaction->ref_id)?->delete();
                    break;
                case 'inst_payment':
                    InstallmentPayment::find($transaction->ref_id)?->delete();
                    break;
                case 'inst_down':
                    InstallmentContract::find($transaction->ref_id)?->delete();
                    break;
                case 'debt':
                    $debt = SupplierDebt::find($transaction->ref_id);
                    if ($debt) {
                        $newPaid = max(0, (float) $debt->paid_amount - (float) $transaction->amount);
                        $debt->update([
                            'paid_amount' => $newPaid,
                            'status'      => $newPaid <= 0.009
                                ? 'pending'
                                : ($newPaid >= (float) $debt->total_amount - 0.009 ? 'paid' : 'partial'),
                        ]);
                    }
                    $transaction->delete();
                    break;
                default: // manual, client_payment, null, band (legacy)
                    $transaction->delete();
            }
        });

        return back()->with('success', 'تم حذف الحركة وعكس كل أثرها بالكامل.');
    }

    private function verifyAdminPassword(string $password): void
    {
        if (! Hash::check($password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة مرور الأدمن غير صحيحة.',
            ]);
        }
    }
}
