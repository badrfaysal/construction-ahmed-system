<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectBandController extends Controller
{
    // Show form to add a new band (work phase) to a project
    public function create(Project $project)
    {
        // مشروع اتقسّط بالكامل يقفل إضافة بنود جديدة خالص — رجّع قبل ما نعرض
        // الفورم أصلاً بدل ما المستخدم يملاها ويكتشف الرفض بعد كده
        if ($project->hasWholeProjectInstallmentContract()) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'تم تقسيط المشروع بالكامل — لا يمكن إضافة بنود جديدة لهذا المشروع.');
        }

        $wallets   = Account::selectable();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'activity']);
        $itemNames = MaterialController::knownItemNames();
        $unitNames = MaterialController::knownUnitNames();
        
        $bandNames = \App\Models\ProjectBand::select('name')->distinct()->pluck('name')
            ->merge(\App\Models\QuoteBand::select('name')->distinct()->pluck('name'))
            ->unique()->sort()->values();

        $knownWorkersJson = \App\Models\BandWorker::select('name', 'phone', 'specialty')
            ->groupBy('name', 'phone', 'specialty')
            ->get()->unique('name')->values()->toJson();

        return view('bands.create', compact('project', 'wallets', 'suppliers', 'itemNames', 'unitNames', 'bandNames', 'knownWorkersJson'));
    }

    // Save a new band under the given project — labor (workers), materials
    // and نثريات can all be registered together in one go, so "سعر البند
    // الأولي" reflects the full real cost (مصنعية + خامات + نثريات) from
    // the moment the band is created, not just labor.
    public function store(Request $request, Project $project)
    {
        if ($project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'name' => 'تم تقسيط المشروع بالكامل — لا يمكن إضافة بنود جديدة لهذا المشروع.',
            ]);
        }

        $this->stripEmptyWorkers($request);
        $this->stripEmptyMaterials($request);
        $data = $this->validateData($request);
        $workers   = $data['workers'] ?? [];
        $materials = $data['materials'] ?? [];
        $misc      = $data['misc'] ?? [];
        unset($data['workers'], $data['materials'], $data['misc']);
        $payment = [
            'purchase_date'   => $data['purchase_date'] ?? today()->toDateString(),
            'payment_status'  => $data['payment_status'] ?? 'paid',
            'account_id'      => $data['account_id'] ?? null,
            'paid_amount'     => $data['paid_amount'] ?? 0,
            'invoice_name'    => $data['invoice_name'] ?? null,
            'supplier_id'     => $data['supplier_id'] ?? null,
        ];
        unset($data['purchase_date'], $data['payment_status'], $data['account_id'], $data['paid_amount'], $data['invoice_name'], $data['supplier_id']);

        // الحالة بقت ثنائية بس: كل بند بيتسجل "جاري" تلقائيًا، ويتحول "منفذ" لاحقًا
        // من زرار "إنهاء البند" في صفحة المشروع — مفيش خيار حالة وقت الإنشاء
        $data['status'] = 'active';

        $hasItems = count($materials) > 0 || count($misc) > 0;

        // لازم تختار محفظة لو هتشتري خامات/نثريات دلوقتي (إلا لو آجل بالكامل)
        if ($hasItems && $payment['payment_status'] !== 'deferred' && empty($payment['account_id'])) {
            throw ValidationException::withMessages([
                'account_id' => 'اختر المحفظة التي سيتم الصرف منها للخامات/النثريات.',
            ]);
        }

        // مبلغ الدفع الجزئي مايتجاوزش تكلفة الخامات+النثريات الفعلية
        if ($hasItems && $payment['payment_status'] === 'partial') {
            $totalCost = $this->itemsCost($materials) + $this->itemsCost($misc, isMisc: true);
            if ((float) $payment['paid_amount'] > $totalCost + 0.01) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'المبلغ المدفوع أكبر من إجمالي تكلفة الخامات والنثريات (' . number_format($totalCost, 2) . ' ج.م).',
                ]);
            }
        }

        // New bands go to the end of the list
        $data['sort_order'] = ($project->bands()->max('sort_order') ?? 0) + 1;

        DB::transaction(function () use ($project, $data, $workers, $materials, $misc, $payment) {
            $band = $project->bands()->create($data);
            $band->syncLabor($workers);
            
            $invoiceId = null;
            if (count($materials) > 0 && !empty($payment['invoice_name'])) {
                $matCost = $this->itemsCost($materials);
                $miscCost = $this->itemsCost($misc, true);
                $totalCost = $matCost + $miscCost;
                
                $matPaid = 0;
                if ($payment['payment_status'] === 'paid') {
                    $matPaid = $matCost;
                } elseif ($payment['payment_status'] === 'partial' && $totalCost > 0) {
                    $matPaid = round($payment['paid_amount'] * ($matCost / $totalCost), 2);
                }

                $invoice = \App\Models\MaterialInvoice::create([
                    'project_id'   => $project->id,
                    'supplier_id'  => $payment['supplier_id'] ?? null,
                    'account_id'   => $payment['payment_status'] === 'deferred' ? null : $payment['account_id'],
                    'date'         => $payment['purchase_date'],
                    'name'         => $payment['invoice_name'],
                    'total_amount' => $matCost,
                    'paid_amount'  => $matPaid,
                ]);
                $invoiceId = $invoice->id;
            }

            $this->createBandItems($band, $materials, $misc, $payment, $invoiceId);
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم إضافة البند بنجاح.');
    }

    // Total gross cost (qty × unit_price) across a list of item rows — used
    // both to cap a partial payment and to distribute it proportionally
    private function itemsCost(array $items, bool $isMisc = false): float
    {
        return array_sum(array_map(
            fn ($i) => $isMisc ? (float) $i['amount'] : (float) $i['qty'] * (float) $i['unit_price'],
            $items
        ));
    }

    // Creates Material rows (real خامات + نثري misc expenses) for a
    // freshly-created band, sharing one payment method across all of them —
    // mirrors MaterialController::store()'s proportional partial-payment split.
    private function createBandItems(ProjectBand $band, array $materials, array $misc, array $payment, ?int $invoiceId = null): void
    {
        $rows = collect($materials)->map(fn ($m) => [
            'category'        => 'material',
            'item'            => $m['item'],
            'supplier_id'     => $m['supplier_id'] ?? null,
            'unit'            => $m['unit'],
            'qty'             => $m['qty'],
            'unit_price'      => $m['unit_price'],
            'sell_price'      => $m['sell_price'],
            'supervision_pct' => $m['supervision_pct'] ?? 0,
        ])->concat(collect($misc)->map(fn ($m) => [
            'category'        => 'misc',
            'item'            => $m['item'],
            'supplier_id'     => null,
            'unit'            => 'مبلغ',
            'qty'             => 1,
            'unit_price'      => $m['amount'],
            'sell_price'      => $m['sell_price'],
            'supervision_pct' => $m['supervision_pct'] ?? 0,
        ]));

        if ($rows->isEmpty()) {
            return;
        }

        $totalCost = $rows->sum(fn ($r) => (float) $r['qty'] * (float) $r['unit_price']);
        $paidAmount = (float) $payment['paid_amount'];

        foreach ($rows as $row) {
            $itemCost = (float) $row['qty'] * (float) $row['unit_price'];
            $itemPaid = $payment['payment_status'] === 'partial' && $totalCost > 0
                ? round($paidAmount * ($itemCost / $totalCost), 2)
                : 0;

            Material::create([
                'project_id'      => $band->project_id,
                'band_id'         => $band->id,
                'account_id'      => $payment['payment_status'] === 'deferred' ? null : $payment['account_id'],
                'invoice_id'      => $invoiceId,
                'supplier_id'     => $payment['supplier_id'] ?? null,
                'category'        => $row['category'],
                'item'            => $row['item'],
                'unit'            => $row['unit'],
                'qty'             => $row['qty'],
                'unit_price'      => $row['unit_price'],
                'sell_price'      => $row['sell_price'],
                'supervision_pct' => $row['supervision_pct'],
                'date'            => $payment['purchase_date'],
                'payment_status'  => $payment['payment_status'],
                'paid_amount'     => $itemPaid,
            ]);
        }
    }

    // Show edit form for one band
    public function edit(ProjectBand $band)
    {
        $band->load('workers.payments');
        $legacySeed = $band->legacyWorkerSeed();
        
        $knownWorkersJson = \App\Models\BandWorker::select('name', 'phone', 'specialty')
            ->groupBy('name', 'phone', 'specialty')
            ->get()->unique('name')->values()->toJson();

        $invoices = \App\Models\MaterialInvoice::with('supplier')->orderByDesc('date')->get(['id', 'supplier_id', 'name', 'date']);

        return view('bands.edit', compact('band', 'legacySeed', 'knownWorkersJson', 'invoices'));
    }

    // Save edits to a band
    public function update(Request $request, ProjectBand $band)
    {
        if ($band->hasInstallmentContract() || $band->project->hasWholeProjectInstallmentContract()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'لا يمكن تعديل هذا البند لأنه مرتبط بعقد تقسيط. قم بإلغاء عقد التقسيط أولاً.',
            ]);
        }

        $this->stripEmptyWorkers($request);
        $data = $this->validateData($request);
        $workers = $data['workers'] ?? [];
        unset($data['workers']);

        DB::transaction(function () use ($band, $data, $workers) {
            $band->update($data);
            $band->syncLabor($workers);
        });

        return redirect()->route('projects.show', $band->project)
            ->with('success', 'تم تحديث البند.');
    }

    // Quick status change from the project page's bands list — touches only
    // status, never the workers list, so it can never wipe a band's technicians
    // (unlike the full edit form's update(), which always resyncs workers).
    public function updateStatus(Request $request, ProjectBand $band)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,active,done'],
        ]);

        $band->update($data);

        return back()->with('success', 'تم تحديث حالة البند.');
    }

    // Delete a band (its materials lose the band link, see migration nullOnDelete)
    public function destroy(ProjectBand $band)
    {
        if ($band->hasInstallmentContract() || $band->project->hasWholeProjectInstallmentContract()) {
            return back()->with('error', 'لا يمكن حذف هذا البند لأنه مرتبط بعقد تقسيط. قم بإلغاء التقسيط أولاً.');
        }

        $project = $band->project;

        // Deleting a band DB-cascades its workers AND their recorded دفعات —
        // real paid cash would vanish from the books while its wallet debits
        // stay behind. Block until those payments are removed explicitly.
        if ($band->workers()->whereHas('payments')->exists()) {
            return back()->with('error', 'البند فيه صنايعية ليهم دفعات مسجلة — احذف دفعاتهم الأول (من صفحة دفعات كل صنايعي) قبل حذف البند.');
        }

        DB::transaction(fn () => $band->delete());

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم حذف البند.');
    }

    // JSON list of a project's bands — used by materials/create.blade.php
    // to refill the band dropdown when the project select changes. Flags
    // bands already under their own installment contract so the UI can grey
    // them out (buying materials into them is blocked server-side too).
    public function bandsJson(Project $project)
    {
        return $project->bands()->orderBy('sort_order')->get(['id', 'name'])
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'has_contract' => $b->hasInstallmentContract()]);
    }

    // A band always ships with at least one blank worker row in the UI as a
    // starting point (see bands/create.blade.php's addWorker()) — if the user
    // only meant to change the name/price/status and never touched it, that
    // untouched row would otherwise fail "workers.*.name required" and 422
    // the whole save silently (nothing in the form told them it failed).
    // Dropping fully-empty rows before validating lets a band with zero real
    // workers still save fine — syncLabor() already handles that case.
    private function stripEmptyWorkers(Request $request): void
    {
        $workers = collect($request->input('workers', []))
            ->filter(fn ($w) => trim($w['name'] ?? '') !== '')
            ->values()
            ->all();
        $request->merge(['workers' => $workers]);
    }

    // Same idea for the optional خامات/نثريات rows in the create form — an
    // untouched blank row must not 422 the whole save
    private function stripEmptyMaterials(Request $request): void
    {
        $materials = collect($request->input('materials', []))
            ->filter(fn ($m) => trim($m['item'] ?? '') !== '')
            ->values()->all();
        $request->merge(['materials' => $materials]);

        $misc = collect($request->input('misc', []))
            ->filter(fn ($m) => trim($m['item'] ?? '') !== '')
            ->values()->all();
        $request->merge(['misc' => $misc]);
    }

    // Shared validation rules for store() and update() — every band's labor
    // is now always a list of technicians (no more band-level simple team/day-rate path)
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'client_price'  => ['required', 'numeric', 'min:0'],
            // بقت اختيارية — بند جديد بيتسجل "جاري" تلقائيًا (شايف store())،
            // وفورم التعديل لسه ممكن يبعتها لتغيير الحالة يدويًا
            'status'        => ['nullable', 'in:pending,active,done'],
            'workers'                      => ['nullable', 'array'],
            // id present = update that worker in place (keeps his دفعات) —
            // syncLabor() scopes the lookup to the band's own workers
            'workers.*.id'                 => ['nullable', 'integer'],
            'workers.*.name'               => ['required', 'string', 'max:255'],
            'workers.*.phone'              => [
                'nullable', 
                'string', 
                'max:30', 
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $id = $request->input("workers.$index.id");
                    $name = $request->input("workers.$index.name");
                    $rule = new \App\Rules\UniquePhone('sy2_band_workers', $id, $name);
                    $rule->validate($attribute, $value, $fail);
                }
            ],
            'workers.*.specialty'          => ['nullable', 'string', 'max:255'],
            'workers.*.contract_type'      => ['nullable', 'in:lump_sum,daily,per_meter,per_piece'],
            'workers.*.contract_qty'       => ['nullable', 'numeric', 'min:0'],
            'workers.*.contract_unit_rate' => ['nullable', 'numeric', 'min:0'],
            'workers.*.sell_rate'          => ['nullable', 'numeric', 'min:0'],
            'workers.*.amount'             => ['nullable', 'numeric', 'min:0'],
            'workers.*.sell_amount'        => ['nullable', 'numeric', 'min:0'],
            'workers.*.supervision_pct'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'workers.*.start_date'         => ['nullable', 'date'],
            'workers.*.notes'              => ['nullable', 'string'],

            // خامات اختيارية بتتضاف وقت إنشاء البند — بتدخل في سعر البند الأولي
            // كل خامة ممكن تتشترى من مورد مختلف
            'materials'                    => ['nullable', 'array'],
            'materials.*.item'             => ['required', 'string', 'max:255'],
            'materials.*.unit'             => ['required', 'string', 'max:50'],
            'materials.*.qty'              => ['required', 'numeric', 'min:0'],
            'materials.*.unit_price'       => ['required', 'numeric', 'min:0'],
            'materials.*.sell_price'       => ['required', 'numeric', 'min:0'],
            'materials.*.supervision_pct'  => ['nullable', 'numeric', 'min:0', 'max:100'],

            // نثريات اختيارية (إكرامية/نقل...) — بنفس منطق الخامات
            'misc'                         => ['nullable', 'array'],
            'misc.*.item'                  => ['required', 'string', 'max:255'],
            'misc.*.amount'                => ['required', 'numeric', 'min:0'],
            'misc.*.sell_price'            => ['required', 'numeric', 'min:0'],
            'misc.*.supervision_pct'       => ['nullable', 'numeric', 'min:0', 'max:100'],

            // طريقة دفع مشتركة للخامات والنثريات (لو اتضافوا)
            'invoice_name'   => ['nullable', 'string', 'max:255', 'unique:sy2_material_invoices,name'],
            'supplier_id'    => ['nullable', 'exists:sy2_suppliers,id'],
            'purchase_date'  => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:paid,partial,deferred'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'paid_amount'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'invoice_name.unique' => 'اسم الفاتورة مسجل مسبقاً، يرجى تغييره لعدم التكرار.',
        ]);
    }
}
