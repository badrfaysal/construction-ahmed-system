<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Installment;              // النظام القديم — للمستحقات/التنبيهات فقط
use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// منظومة العقود والأقساط (بروح السيستم الأول) — العقد هنا مربوط بمشروع/عميل.
// كل عقد = خطة سداد (إجمالي/مقدم/شهور/قسط)، والدفعات بتتحصّل عليه بمرور الوقت
// وبتغذّي محفظة المقاولات تلقائيًا عبر InstallmentPaymentObserver.
class InstallmentController extends Controller
{
    // الشاشة الرئيسية: عقود نشطة (مجمّعة بالعميل في الواجهة) + منتهية + إحصائيات
    public function index(Request $request)
    {
        $contracts = InstallmentContract::with(['project.client', 'payments'])
            ->orderByDesc('id')
            ->get();

        $active    = $contracts->filter(fn ($c) => (float) $c->remaining_balance > 0.009);
        $completed = $contracts->filter(fn ($c) => (float) $c->remaining_balance <= 0.009);

        $totalOut       = $active->sum(fn ($c) => (float) $c->remaining_balance);
        $totalCollected = $contracts->sum(fn ($c) => (float) $c->down_payment + (float) $c->payments->sum('amount_paid'));

        $todayDay          = (int) date('d');
        $todayContracts    = $active->where('due_day', $todayDay);

        // كل المشاريع متاحة لعمل عقد جديد (يُسمح بعقد للمشروع كامل أو لبند محدد،
        // وبأكثر من عقد للمشروع الواحد) + قيمة فاتورة كل مشروع/بند للعميل
        $projectsForContract = Project::with(['client', 'bands', 'clientPayments', 'contracts'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($p) {
                $billed = $p->actualClientTotal();

                // فلوس اتحصّلت من العميل مباشرة (صفحة المستحقات) — بتتملى تلقائي
                // في «المقدم المدفوع الآن» وقت إنشاء العقد، بمعياريْن:
                //  - already_paid: نطاق ضيّق (تحت البند المختار بس، أو دفعات
                //    عامة لو المشروع كامل)، ده الافتراضي.
                //  - already_paid_total: كل فلوس المشروع مهما كان البند —
                //    بيتفعّل لما المستخدم يحدد checkbox «اعتبر كل المبلغ...».
                // في الحالتين ناقص أي مقدم اتسجّل بالفعل لعقود سابقة (عشان
                // مايتحسبش مرتين). كل دفعة = amount + discount (الخصم بيتحسب
                // كأنه اتحصّل برضو، بروح Project::totalCollected()).
                $paidSum = fn ($payments) => (float) $payments->sum(fn ($t) => (float) $t->amount + (float) $t->discount);

                $totalPaidAll = $paidSum($p->clientPayments);
                $totalClaimed = (float) $p->contracts->sum('down_payment');
                $totalUnclaimed = max(0, $totalPaidAll - $totalClaimed);

                return (object) [
                    'id'                 => $p->id,
                    'name'               => $p->name,
                    'client_name'        => $p->client->name ?? '',
                    'client_phone'       => $p->client->phone ?? '',
                    'billed'             => round($billed, 2),
                    'already_paid'       => round($totalUnclaimed, 2),
                    'already_paid_total' => round($totalUnclaimed, 2),
                    // بنود المشروع مع قيمة كل بند للعميل — لتقسيط بند محدد
                    'bands'         => $p->bands->map(function ($b) use ($p, $paidSum, $totalUnclaimed) {
                        $bandPaid = max(0,
                            $paidSum($p->clientPayments->where('band_id', $b->id))
                            - (float) $p->contracts->where('band_id', $b->id)->sum('down_payment')
                        );
                        return (object) [
                            'id'                 => $b->id,
                            'name'               => $b->name,
                            'billed'             => round($b->actualClientTotal(), 2),
                            'already_paid'       => round($bandPaid, 2),
                            'already_paid_total' => round($totalUnclaimed, 2),
                        ];
                    })->values(),
                ];
            });

        $wallets = Account::selectable();

        return view('installments.index', compact(
            'contracts', 'active', 'completed',
            'totalOut', 'totalCollected', 'todayContracts', 'projectsForContract', 'wallets'
        ));
    }

    // إنشاء عقد جديد لمشروع — الإجمالي بييجي من فاتورة العميل (قابل للتعديل)
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'         => ['required', 'exists:sy2_projects,id'],
            'band_id'            => ['nullable', 'exists:sy2_project_bands,id'],
            'account_id'         => ['nullable', 'integer', 'exists:accounts,id'],
            'product_name'       => ['nullable', 'string', 'max:255'],
            'cash_price'         => ['required', 'numeric', 'min:0'],
            'discount'           => ['nullable', 'numeric', 'min:0'],
            'down_payment'       => ['nullable', 'numeric', 'min:0'],
            'interest_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_months' => ['required', 'integer', 'min:1', 'max:240'],
            'due_day'            => ['required', 'integer', 'min:1', 'max:31'],
            'start_date'         => ['required', 'date'],
            'notes'              => ['nullable', 'string'],
        ]);

        $project = Project::with('client')->findOrFail($data['project_id']);

        // اسم المتعاقد عليه: المشروع كامل، أو "المشروع — البند" لو اتقسّط بند محدد
        $productName = trim((string) ($data['product_name'] ?? '')) ?: $project->name;
        if (empty($data['product_name']) && ! empty($data['band_id'])) {
            $band = $project->bands()->whereKey($data['band_id'])->first();
            if ($band) {
                $productName = $project->name . ' — ' . $band->name;
            }
        }

        $cash     = (float) $data['cash_price'];
        $disc     = (float) ($data['discount'] ?? 0);
        $down     = (float) ($data['down_payment'] ?? 0);
        $rate     = (float) ($data['interest_rate'] ?? 0);
        $months   = (int) $data['installment_months'];

        // نفس معادلة السيستم الأول
        $afterDisc      = max(0, $cash - $disc);
        $baseForInt     = max(0, $afterDisc - $down);
        $interestVal    = $baseForInt * ($rate / 100);
        $totalAfterInt  = $afterDisc + $interestVal;      // شامل المقدم
        $remaining      = max(0, $totalAfterInt - $down);
        $monthly        = $months > 0 ? $remaining / $months : 0;

        if ($down > $afterDisc + 0.01) {
            throw ValidationException::withMessages([
                'down_payment' => 'المقدم أكبر من قيمة العقد بعد الخصم.',
            ]);
        }

        DB::transaction(fn () => InstallmentContract::create([
            'project_id'           => $project->id,
            'band_id'              => $data['band_id'] ?? null,
            'account_id'           => $data['account_id'] ?? null,
            'customer_name'        => $project->client->name ?? 'عميل',
            'customer_phone'       => $project->client->phone,
            'product_name'         => $productName,
            'cash_price'           => $cash,
            'discount'             => $disc,
            'down_payment'         => $down,
            'interest_rate'        => $rate,
            'installment_months'   => $months,
            'total_after_interest' => round($totalAfterInt, 2),
            'monthly_installment'  => round($monthly, 2),
            'due_day'              => (int) $data['due_day'],
            'remaining_balance'    => round($remaining, 2),
            'start_date'           => $data['start_date'],
            'status'               => 'active',
            'notes'                => $data['notes'] ?? null,
        ]));

        return redirect()->route('installments.index')
            ->with('success', 'تم إنشاء عقد التقسيط بنجاح.');
    }

    // تسجيل دفعة تحصيل على عقد (تحصيل كامل / قسط شهري / جزئي + خصم اختياري)
    public function pay(Request $request, InstallmentContract $contract)
    {
        // Manual Validator (not $request->validate()) + explicit try/catch
        // around the business-rule check below — both paths need to flash
        // reopen_phone/reopen_name on FAILURE too, not just success, so the
        // customer's statement modal reopens with the error visible instead
        // of silently closing and losing what was typed.
        $validator = Validator::make($request->all(), [
            'amount_paid'      => ['required', 'numeric', 'min:0'],
            'discount_applied' => ['nullable', 'numeric', 'min:0'],
            'account_id'       => ['required', 'integer', 'exists:accounts,id'],
            'payment_date'     => ['required', 'date'],
            'method'           => ['nullable', 'string', 'max:50'],
            'notes'            => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('reopen_phone', $contract->customer_phone)
                ->with('reopen_name', $contract->customer_name)
                ->with('reopen_contract_id', $contract->id)
                ->with('reopen_form', 'pay');
        }

        $data = $validator->validated();
        $amount   = (float) $data['amount_paid'];
        $discount = (float) ($data['discount_applied'] ?? 0);

        if ($amount <= 0 && $discount <= 0) {
            return back()->withErrors(['amount_paid' => 'أدخل مبلغ تحصيل أو خصم.'])->withInput()
                ->with('reopen_phone', $contract->customer_phone)
                ->with('reopen_name', $contract->customer_name)
                ->with('reopen_contract_id', $contract->id)
                ->with('reopen_form', 'pay');
        }

        try {
            // Lock the contract row for the duration of the check + insert so
            // two concurrent/double-click submissions can't both pass the
            // remaining-balance check before either one writes (which would
            // overpay it).
            DB::transaction(function () use ($contract, $amount, $discount, $data) {
                $locked = InstallmentContract::whereKey($contract->id)->lockForUpdate()->firstOrFail();

                if ($amount + $discount > (float) $locked->remaining_balance + 0.01) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'المجموع (تحصيل + خصم) أكبر من المتبقي على العقد (' . number_format($locked->remaining_balance, 2) . ' ج.م).',
                    ]);
                }

                InstallmentPayment::create([
                    'contract_id'      => $contract->id,
                    'project_id'       => $contract->project_id,
                    'account_id'       => $data['account_id'] ?? $contract->account_id,
                    'amount_paid'      => $amount,
                    'discount_applied' => $discount,
                    'payment_date'     => $data['payment_date'],
                    'method'           => $data['method'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput()
                ->with('reopen_phone', $contract->customer_phone)
                ->with('reopen_name', $contract->customer_name)
                ->with('reopen_contract_id', $contract->id)
                ->with('reopen_form', 'pay');
        }

        return back()
            ->with('success', 'تم تسجيل الدفعة.')
            ->with('reopen_phone', $contract->customer_phone)
            ->with('reopen_name', $contract->customer_name);
    }

    // دفع جماعي: يسدّد القسط الشهري لكل عقد من العقود المحددة دفعة واحدة
    public function payBulk(Request $request)
    {
        $data = $request->validate([
            'contract_ids'   => ['required', 'array', 'min:1'],
            'contract_ids.*' => ['integer', 'exists:sy2_installment_contracts,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'payment_date'   => ['required', 'date'],
            'method'         => ['nullable', 'string', 'max:50'],
        ]);

        $count = 0;
        DB::transaction(function () use ($data, &$count) {
            $contracts = InstallmentContract::whereIn('id', $data['contract_ids'])->get();
            foreach ($contracts as $contract) {
                $remaining = (float) $contract->remaining_balance;
                if ($remaining <= 0.009) {
                    continue;
                }
                $pay = min((float) $contract->monthly_installment, $remaining);
                if ($pay <= 0) {
                    continue;
                }
                InstallmentPayment::create([
                    'contract_id'  => $contract->id,
                    'project_id'   => $contract->project_id,
                    'account_id'   => $data['account_id'] ?? $contract->account_id,
                    'amount_paid'  => $pay,
                    'payment_date' => $data['payment_date'],
                    'method'       => $data['method'] ?? null,
                    'notes'        => 'دفع جماعي — قسط شهري',
                ]);
                $count++;
            }
        });

        return back()->with('success', "تم تسجيل $count دفعة (سداد جماعي للأقساط الشهرية).");
    }

    // عكس دفعة اتسجلت بالغلط — يرجّع المتبقي على العقد ويعكس المحفظة
    public function reversePayment(InstallmentPayment $payment)
    {
        $contract = $payment->contract;
        DB::transaction(fn () => $payment->delete());

        return back()
            ->with('success', 'تم إلغاء الدفعة وإرجاع المبلغ.')
            ->with('reopen_phone', $contract?->customer_phone)
            ->with('reopen_name', $contract?->customer_name);
    }

    // حذف عقد بالكامل (يعكس المقدم وكل دفعاته من المحفظة عبر الـ observer)
    public function destroy(InstallmentContract $contract)
    {
        DB::transaction(fn () => $contract->delete());

        return redirect()->route('installments.index')->with('success', 'تم حذف العقد.');
    }

    // تعديل عقد قائم — يعيد حساب الخطة ويزامن المقدم في المحفظة لو اتغيّر
    public function update(Request $request, InstallmentContract $contract)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_phone'     => ['nullable', 'string', 'max:30'],
            'product_name'       => ['nullable', 'string', 'max:255'],
            'cash_price'         => ['required', 'numeric', 'min:0'],
            'discount'           => ['nullable', 'numeric', 'min:0'],
            'down_payment'       => ['nullable', 'numeric', 'min:0'],
            'interest_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_months' => ['required', 'integer', 'min:1', 'max:240'],
            'due_day'            => ['required', 'integer', 'min:1', 'max:31'],
            'start_date'         => ['required', 'date'],
            'notes'              => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('reopen_phone', $contract->customer_phone)
                ->with('reopen_name', $contract->customer_name)
                ->with('reopen_contract_id', $contract->id)
                ->with('reopen_form', 'edit');
        }

        $data = $validator->validated();
        $cash   = (float) $data['cash_price'];
        $disc   = (float) ($data['discount'] ?? 0);
        $down   = (float) ($data['down_payment'] ?? 0);
        $rate   = (float) ($data['interest_rate'] ?? 0);
        $months = (int) $data['installment_months'];

        $afterDisc     = max(0, $cash - $disc);
        $baseForInt    = max(0, $afterDisc - $down);
        $totalAfterInt = $afterDisc + $baseForInt * ($rate / 100);
        $monthly       = $months > 0 ? ($totalAfterInt - $down) / $months : 0;

        if ($down > $afterDisc + 0.01) {
            return back()->withErrors(['down_payment' => 'المقدم أكبر من قيمة العقد بعد الخصم.'])->withInput()
                ->with('reopen_phone', $contract->customer_phone)
                ->with('reopen_name', $contract->customer_name)
                ->with('reopen_contract_id', $contract->id)
                ->with('reopen_form', 'edit');
        }

        // المتبقي بعد التعديل = (الإجمالي − المقدم) − اللي اتحصّل فعلاً على العقد
        $paid      = (float) $contract->payments()->sum('amount_paid') + (float) $contract->payments()->sum('discount_applied');
        $remaining = max(0, $totalAfterInt - $down - $paid);

        DB::transaction(function () use ($contract, $data, $down, $totalAfterInt, $monthly, $remaining) {
            $oldDown = (float) $contract->down_payment;

            $contract->update([
                'customer_name'        => $data['customer_name'],
                'customer_phone'       => $data['customer_phone'] ?? null,
                'product_name'         => $data['product_name'] ?: $contract->product_name,
                'cash_price'           => $data['cash_price'],
                'discount'             => $data['discount'] ?? 0,
                'down_payment'         => $down,
                'interest_rate'        => $data['interest_rate'] ?? 0,
                'installment_months'   => $data['installment_months'],
                'total_after_interest' => round($totalAfterInt, 2),
                'monthly_installment'  => round($monthly, 2),
                'due_day'              => (int) $data['due_day'],
                'remaining_balance'    => round($remaining, 2),
                'start_date'           => $data['start_date'],
                'notes'                => $data['notes'] ?? null,
            ]);

            // زامن حركة المقدم في المحفظة لو المقدم اتغيّر (Model instance عشان
            // TransactionObserver يعدّل المحفظة)
            if (abs($down - $oldDown) > 0.001) {
                $tx = Transaction::where('ref_type', 'inst_down')->where('ref_id', $contract->id)->first();
                if ($down > 0) {
                    if ($tx) {
                        $tx->update(['amount' => $down, 'date' => $data['start_date'], 'party' => $data['customer_name'], 'account_id' => $contract->account_id]);
                    } else {
                        Transaction::create([
                            'project_id' => $contract->project_id, 'account_id' => $contract->account_id, 'direction' => 'in',
                            'type' => 'تحصيل مقدم', 'party' => $data['customer_name'],
                            'amount' => $down, 'date' => $data['start_date'],
                            'description' => 'مقدم عقد تقسيط — ' . $contract->product_name,
                            'ref_type' => 'inst_down', 'ref_id' => $contract->id,
                        ]);
                    }
                } elseif ($tx) {
                    $tx->delete();
                }
            }
        });

        return redirect()->route('installments.index')
            ->with('success', 'تم تعديل العقد.')
            ->with('reopen_phone', $contract->customer_phone)
            ->with('reopen_name', $contract->customer_name);
    }

    // (النظام القديم) تحديد قسط مشروع كمدفوع — تستخدمه شاشتا المستحقات والتنبيهات
    public function markPaid(Installment $installment)
    {
        DB::transaction(fn () => $installment->update([
            'status'    => 'paid',
            'paid_date' => today(),
        ]));

        return back()->with('success', 'تم تسجيل الدفع.');
    }

    // كشف حساب عميل (يُحمّل عند الطلب AJAX) — كل عقوده + جداولها + دفعاتها + أزرار السداد
    public function customerStatement(Request $request)
    {
        $phone = trim((string) $request->get('phone', ''));
        $name  = trim((string) $request->get('name', ''));

        $query = InstallmentContract::with(['payments', 'project.client']);
        if ($phone !== '' && $phone !== '—') {
            $query->where('customer_phone', $phone);
        } else {
            $query->where('customer_name', $name);
        }
        $contracts = $query->orderByDesc('id')->get();

        $customerName  = $contracts->first()->customer_name ?? $name;
        $customerPhone = $contracts->first()->customer_phone ?? $phone;
        $wallets       = Account::selectable();

        return view('installments._statement', compact('contracts', 'customerName', 'customerPhone', 'wallets'));
    }
}
