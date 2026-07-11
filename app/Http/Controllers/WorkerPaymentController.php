<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BandWorker;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkerPaymentController extends Controller
{
    // Manage one craftsman's installments (دفعات): his contract, what he's been
    // paid, what's still due, and the form to record the next payment.
    public function show(BandWorker $worker)
    {
        $worker->load(['payments', 'band.project']);
        $wallets = Account::selectable();

        return view('worker-payments.show', compact('worker', 'wallets'));
    }

    // Record a دفعة to this craftsman — hits محفظة المقاولات immediately via
    // WorkerPaymentObserver (wrapped so an insufficient-funds rejection rolls
    // the payment row back too).
    public function store(Request $request, BandWorker $worker)
    {
        $data = $request->validate([
            // الكاش ممكن يكون صفر لو ده خصم بس (بدون فلوس بتتصرف من المحفظة)
            'amount'          => ['nullable', 'numeric', 'min:0'],
            'discount'        => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => [
                'nullable', 'string', 'max:500',
                function ($attribute, $value, $fail) use ($request) {
                    if ((float) $request->input('discount', 0) > 0 && trim((string) $value) === '') {
                        $fail('لازم تكتب سبب الخصم.');
                    }
                },
            ],
            'account_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'date'            => ['required', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);

        // لازم يكون فيه كاش أو خصم — مش الاتنين صفر
        if ((float) $data['amount'] <= 0 && (float) ($data['discount'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'اكتب مبلغ كاش أو خصم (مش ينفع الاتنين صفر).',
            ]);
        }

        // فيه كاش بيتصرف؟ لازم تختار المحفظة. خصم بس (كاش صفر) مايحتاجش محفظة.
        if ((float) $data['amount'] > 0 && empty($data['account_id'])) {
            throw ValidationException::withMessages([
                'account_id' => 'اختر المحفظة اللي هيتصرف منها الكاش.',
            ]);
        }

        // لازم يكون فيه تعاقد الأول عشان نعرف نحسب المتبقي عليه
        if ((float) $worker->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'لازم تحدد قيمة تعاقد الصنايعي الأول قبل ما تسجّل له أي دفعة.',
            ]);
        }

        $remaining = $worker->remaining();

        // الكاش (الفلوس اللي بتطلع فعلاً) ما ينفعش يتعدى المتبقي عليه — مش
        // منطقي تدفعه كاش أكتر من مستحقه. الخصم بس هو اللي مسموح يتعدى
        // المتبقي، وساعتها الفرق بيبقى "مستحق لينا عنده" (شايف تحت).
        if ((float) $data['amount'] > $remaining + 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'المبلغ الكاش أكبر من المتبقي للصنايعي (' . number_format($remaining, 2) . ' ج.م). الخصم بس هو اللي ممكن يتعدى المتبقي.',
            ]);
        }

        $band = $worker->band;

        DB::transaction(fn () => WorkerPayment::create([
            'band_worker_id'  => $worker->id,
            'project_id'      => $band->project_id,
            'project_band_id' => $band->id,
            'account_id'      => $data['account_id'] ?? null,
            'amount'          => $data['amount'] ?? 0,
            'discount'        => $data['discount'] ?? 0,
            'discount_reason' => $data['discount_reason'] ?? null,
            'date'            => $data['date'],
            'notes'           => $data['notes'] ?? null,
        ]));

        return back()->with('success', 'تم تسجيل دفعة للصنايعي.');
    }

    // تبديل الفني — الصنايعي الأول عمل جزء من الشغل، خد جزء من فلوسه ومشي،
    // وبنجيب صنايعي تاني يكمّل نفس الشغل. بدل ما نمسح الأول (وده ممنوع لأن له
    // دفعات)، بنثبّت تعاقده على اللي استلمه فعلاً (فيبقى مستحقه = صفر)، وننشئ
    // صنايعي جديد في نفس البند بالباقي. إجمالي تكلفة البيع للبند يفضل زي ما هو.
    public function swap(Request $request, BandWorker $worker)
    {
        $data = $request->validate([
            'new_name'   => ['required', 'string', 'max:255'],
            'new_phone'  => ['nullable', 'string', 'max:50'],
            'new_amount' => ['required', 'numeric', 'min:0.01'],
            'date'       => ['required', 'date'],
        ]);

        $paid     = $worker->paidTotal();      // اللي استلمه الأول فعلاً
        $contract = (float) $worker->amount;    // تعاقده الأصلي
        $sell     = (float) $worker->sell_amount; // أساس سعر البيع للعميل

        // لازم يكون فاضل شغل/فلوس يتسلّم للجديد. لو مفيش متبقّي، مفيش تبديل.
        if ($contract <= 0 || $worker->remaining() <= 0.01) {
            throw ValidationException::withMessages([
                'new_amount' => 'مفيش متبقّي على الصنايعي ده يتسلّم لحد تاني — التبديل بيتم لما يكون لسه فاضل جزء من التعاقد.',
            ]);
        }

        // نقسم أساس البيع بنسبة اللي خلصه كل واحد، عشان إجمالي البيع للعميل
        // ما يتغيّرش: الأول ياخد حصة (المدفوع/التعاقد)، والجديد ياخد الباقي.
        $oldSell = $sell > 0 ? round($sell * ($paid / $contract), 2) : 0;
        $newSell = $sell > 0 ? max($sell - $oldSell, 0) : 0;

        $band = $worker->band;

        $newWorker = DB::transaction(function () use ($worker, $band, $data, $paid, $oldSell, $newSell) {
            // (1) نثبّت الأول على اللي استلمه — يبقى مستحقه صفر ومش هيظهر مديون
            $leftNote = 'مشي بعد ما استلم شغله (تبديل بتاريخ ' . $data['date'] . ')';
            $worker->update([
                'contract_type' => 'lump_sum',
                'contract_qty'  => null,
                'amount'        => $paid,
                'sell_amount'   => $oldSell,
                'notes'         => $worker->notes ? $worker->notes . ' · ' . $leftNote : $leftNote,
            ]);

            // (2) صنايعي جديد بالباقي في نفس البند، بيكمّل من حيث وقف الأول
            $created = $band->workers()->create([
                'name'            => $data['new_name'],
                'phone'           => $data['new_phone'] ?? null,
                'specialty'       => $worker->specialty,
                'contract_type'   => 'lump_sum',
                'amount'          => $data['new_amount'],
                'sell_amount'     => $newSell,
                'supervision_pct' => $worker->supervision_pct,
                'start_date'      => $data['date'],
                'notes'           => 'بديل عن ' . $worker->name,
                'sort_order'      => ($band->workers()->max('sort_order') ?? 0) + 1,
            ]);

            // (3) نعيد حساب إجماليات البند من العمال بعد التعديل
            $band->recomputeLaborTotals();

            return $created;
        });

        // نودّي المستخدم على صفحة دفعات الصنايعي الجديد ليسجّل دفعاته
        return redirect()->route('workers.payments', $newWorker)
            ->with('success', 'تم تبديل الفني: "' . $worker->name . '" اتثبّت على اللي استلمه، و"' . $data['new_name'] . '" هيكمّل الباقي.');
    }
}
