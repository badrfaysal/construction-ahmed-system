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

class MaterialController extends Controller
{
    // List all materials across all projects, with optional project filter
    public function index(Request $request)
    {
        $query = Material::with(['project', 'band', 'supplier', 'returns']);

        match ($request->get('sort', 'newest')) {
            'oldest'      => $query->orderBy('date')->orderBy('id'),
            'cost_desc'   => $query->orderByRaw('qty * unit_price DESC'),
            'cost_asc'    => $query->orderByRaw('qty * unit_price ASC'),
            default       => $query->orderByDesc('date')->orderByDesc('id'),
        };

        $pid = $request->get('project_id');
        if ($pid) {
            $query->where('project_id', $pid);
        }

        $materials = $query->paginate(15);
        $projects  = Project::orderBy('name')->get(['id', 'name']);
        $insights  = $this->buildInsights($pid ? (int) $pid : null);

        return view('materials.index', compact('materials', 'projects', 'insights'));
    }

    // أكتر خامة اشتريتها (بالتكلفة)، أكتر خامة عملت لها مرتجع (بالقيمة)، وأكتر
    // بند اشتريت له خامات — بتحترم فلتر المشروع الحالي لو موجود
    private function buildInsights(?int $projectId): array
    {
        $topMaterial = Material::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->selectRaw('item, unit, SUM(qty) as total_qty, SUM(qty * unit_price) as total_cost, COUNT(*) as purchase_count')
            ->groupBy('item', 'unit')
            ->orderByDesc('total_cost')
            ->first();

        $topReturned = DB::table('sy2_material_returns as r')
            ->join('sy2_materials as m', 'm.id', '=', 'r.material_id')
            ->when($projectId, fn ($q) => $q->where('m.project_id', $projectId))
            ->selectRaw('m.item, m.unit, SUM(r.qty) as total_qty, SUM(r.qty * m.unit_price) as total_value, COUNT(*) as return_count')
            ->groupBy('m.item', 'm.unit')
            ->orderByDesc('total_value')
            ->first();

        $topBandRow = Material::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->whereNotNull('band_id')
            ->selectRaw('band_id, SUM(qty * unit_price) as total_cost')
            ->groupBy('band_id')
            ->orderByDesc('total_cost')
            ->first();

        $topBand = null;
        if ($topBandRow) {
            $band = ProjectBand::with('project')->find($topBandRow->band_id);
            if ($band) {
                $topBand = (object) [
                    'band_name'    => $band->name,
                    'project_name' => $band->project->name ?? '—',
                    'total_cost'   => (float) $topBandRow->total_cost,
                ];
            }
        }

        return compact('topMaterial', 'topReturned', 'topBand');
    }

    // Show form to register a batch of purchases: one project, several bands,
    // each band with several items — all saved together in one submission
    public function create(Request $request)
    {
        $projects  = Project::with('contracts')->orderBy('name')->get(['id', 'name', 'default_supervision_pct']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'activity']);
        $wallets   = Account::selectable();
        $itemNames = $this->knownItemNames();
        $unitNames = $this->knownUnitNames();

        // Pre-select a project if provided via query string (e.g. from a project page)
        $selectedProject = $request->get('project_id') ? Project::find($request->get('project_id')) : null;
        $bands = $selectedProject
            ? $selectedProject->bands()->get(['id', 'name'])
                ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'has_contract' => $b->hasInstallmentContract()])
            : collect();

        return view('materials.create', compact('projects', 'suppliers', 'wallets', 'bands', 'selectedProject', 'itemNames', 'unitNames'));
    }

    // كل أسماء الأصناف/الوحدات اللي سبق كتابتها فعلاً — بتغذّي autocomplete
    // حقيقي بدل قائمة ثابتة، مشتركة بين شاشة الخامات وفورم إضافة البند
    public static function knownItemNames()
    {
        return Material::where('category', '!=', 'misc')
            ->whereNotNull('item')->where('item', '!=', '')
            ->distinct()->orderBy('item')->pluck('item');
    }

    public static function knownUnitNames()
    {
        return Material::whereNotNull('unit')->where('unit', '!=', '')
            ->distinct()->orderBy('unit')->pluck('unit');
    }

    // Save every item from every band group in one go
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'                    => ['required', 'exists:sy2_projects,id'],
            'invoice_name'                  => ['required', 'string', 'max:255', 'unique:sy2_material_invoices,name'],
            'date'                          => ['required', 'date'],
            'supplier_id'                   => ['nullable', 'exists:sy2_suppliers,id'],
            'payment_status'                => ['nullable', 'in:paid,partial,deferred'],
            'account_id'                    => ['required_unless:payment_status,deferred', 'nullable', 'integer', 'exists:accounts,id'],
            'paid_amount'                   => ['nullable', 'numeric', 'min:0'],
            'groups'                        => ['required', 'array', 'min:1'],
            'groups.*.band_id'              => ['nullable', 'exists:sy2_project_bands,id'],
            'groups.*.items'                => ['required', 'array', 'min:1'],
            'groups.*.items.*.item'         => ['required', 'string', 'max:255'],
            'groups.*.items.*.unit'         => ['required', 'string', 'max:50'],
            'groups.*.items.*.qty'          => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.sell_price'   => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'invoice_name.unique' => 'اسم الفاتورة مسجل مسبقاً، يرجى تغييره لعدم التكرار.',
        ]);

        // عقد تقسيط للمشروع كامل بيقفل أي فوترة جديدة على المشروع خالص — مفيش
        // خامات ولا بنود بعد كده، عشان مايتلخبطش مع نطاق العقد المتفق عليه.
        $project = Project::findOrFail($data['project_id']);
        if ($project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'project_id' => 'تم تقسيط المشروع بالكامل — لا يمكن شراء خامات جديدة لهذا المشروع.',
            ]);
        }

        // بند معموله عقد تقسيط لوحده بيتقفل هو بس — اعمل بند جديد باسم مختلف
        // لتسجيل خامات جديدة، عشان فوترة الخامة الجديدة متتخلطش مع عقد قايم.
        foreach ($data['groups'] as $i => $group) {
            if (empty($group['band_id'])) {
                continue;
            }
            $band = ProjectBand::find($group['band_id']);
            if ($band && $band->hasInstallmentContract()) {
                throw ValidationException::withMessages([
                    "groups.{$i}.band_id" => 'البند "' . $band->name . '" داخل في عقد تقسيط — اعمل بند جديد باسم جديد لتسجيل خامات عليه.',
                ]);
            }
        }

        // Can't pay more than the group's actual purchase cost — a partial
        // payment above the total would over-debit the wallet for more than
        // what was really bought.
        // We now validate this against the total invoice cost
        $totalCost = 0;
        foreach ($data['groups'] as $group) {
            foreach ($group['items'] as $item) {
                $totalCost += (float)$item['qty'] * (float)$item['unit_price'];
            }
        }

        $paymentStatus = $data['payment_status'] ?? 'paid';
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        if ($paymentStatus === 'partial' && $paidAmount > $totalCost + 0.01) {
            throw ValidationException::withMessages([
                "paid_amount" => 'المبلغ المدفوع أكبر من إجمالي تكلفة الفاتورة (' . number_format($totalCost, 2) . ' ج.م).',
            ]);
        }

        // Wrapped in one transaction so a purchase that would overdraw
        // محفظة المقاولات rolls back the whole batch, not just that one item.
        $count = DB::transaction(function () use ($data, $totalCost, $paidAmount, $paymentStatus) {
            if ($paymentStatus === 'paid') {
                $paidAmount = $totalCost;
            } elseif ($paymentStatus === 'deferred') {
                $paidAmount = 0;
            }

            $invoice = \App\Models\MaterialInvoice::create([
                'project_id'   => $data['project_id'],
                'supplier_id'  => $data['supplier_id'] ?? null,
                'account_id'   => $data['account_id'] ?? null,
                'date'         => $data['date'],
                'name'         => $data['invoice_name'] ?? null,
                'total_amount' => $totalCost,
                'paid_amount'  => $paidAmount,
            ]);

            $count = 0;
            foreach ($data['groups'] as $group) {
                foreach ($group['items'] as $item) {
                    $itemCost = (float)$item['qty'] * (float)$item['unit_price'];
                    $itemPaidAmount = $paymentStatus === 'partial' && $totalCost > 0
                        ? round($paidAmount * ($itemCost / $totalCost), 2)
                        : ($paymentStatus === 'paid' ? $itemCost : 0);

                    \App\Models\Material::create([
                        'project_id'      => $data['project_id'],
                        'band_id'         => $group['band_id'] ?? null,
                        'account_id'      => $data['account_id'] ?? null,
                        'supplier_id'     => $data['supplier_id'] ?? null,
                        'invoice_id'      => $invoice->id,
                        'item'            => $item['item'],
                        'unit'            => $item['unit'],
                        'qty'             => $item['qty'],
                        'unit_price'      => $item['unit_price'],
                        'sell_price'      => $item['sell_price'],
                        'supervision_pct' => $item['supervision_pct'] ?? 0,
                        'date'            => $data['date'],
                        'payment_status'  => $paymentStatus,
                        'paid_amount'     => $itemPaidAmount,
                    ]);
                    $count++;
                }
            }
            return $count;
        });

        return redirect()->route('projects.show', $data['project_id'])
            ->with('success', "تم تسجيل {$count} صنف بنجاح.");
    }


    // Show form to add a miscellaneous expense (نثري) to a project — tips,
    // transport, meals, etc. Defaults the band to the one currently in progress.
    public function createExpense(Project $project)
    {
        $bands       = $project->bands()->orderBy('sort_order')->get(['id', 'name', 'status']);
        $activeBand  = $bands->firstWhere('status', 'active');
        $defaultSup  = $project->defaultSupervisionPct();
        $wallets     = Account::selectable();

        return view('materials.expense', compact('project', 'bands', 'activeBand', 'defaultSup', 'wallets'));
    }

    // Save a misc expense as a Material row (category=misc) so it flows through
    // the wallet, client statement, and cost statement exactly like a purchase.
    public function storeExpense(Request $request, Project $project)
    {
        $isDeferred = $request->input('payment_type') === 'deferred';
        $data = $request->validate([
            'band_id'         => ['nullable', 'exists:sy2_project_bands,id'],
            'account_id'      => [$isDeferred ? 'nullable' : 'required', 'nullable', 'integer', 'exists:accounts,id'],
            'item'            => ['required', 'string', 'max:255'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'sell_price'      => ['required', 'numeric', 'min:0'],
            'supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date'            => ['required', 'date'],
        ]);

        if ($project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'band_id' => 'تم تقسيط المشروع بالكامل — لا يمكن تسجيل خامات أو مصروفات جديدة لهذا المشروع.',
            ]);
        }

        if (! empty($data['band_id'])) {
            $band = ProjectBand::find($data['band_id']);
            if ($band && $band->hasInstallmentContract()) {
                throw ValidationException::withMessages([
                    'band_id' => 'البند "' . $band->name . '" داخل في عقد تقسيط — اعمل بند جديد باسم جديد.',
                ]);
            }
        }

        DB::transaction(fn () => Material::create([
            'project_id'      => $project->id,
            'band_id'         => $data['band_id'] ?? null,
            'account_id'      => $isDeferred ? null : ($data['account_id'] ?? null),
            'category'        => 'misc',
            'item'            => $data['item'],
            'unit'            => 'مبلغ',
            'qty'             => 1,
            'unit_price'      => $data['amount'],
            'sell_price'      => $data['sell_price'],
            'supervision_pct' => $data['supervision_pct'] ?? 0,
            'date'            => $data['date'],
            'payment_status'  => $isDeferred ? 'deferred' : 'paid',
        ]));

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تسجيل المصروف النثري.');
    }

    public function destroy(Request $request, Material $material)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate(['current_password' => ['required', 'string']]);
        if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], auth()->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'كلمة مرور المشرف غير صحيحة.']);
        }

        $projectId = $material->project_id;
        $invoice = $material->invoice;
        $project = $material->project;

        if ($project && $project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'current_password' => 'تم تقسيط المشروع بالكامل — هذا قرار نهائي ولا يمكن عكس أي حركة أو إرجاع خامات تخص هذا المشروع.',
            ]);
        }

        if ($material->band_id) {
            $band = \App\Models\ProjectBand::find($material->band_id);
            if ($band && $band->hasInstallmentContract()) {
                throw ValidationException::withMessages([
                    'current_password' => 'البند التابع له هذه الخامة داخل في عقد تقسيط — هذا قرار نهائي ولا يمكن عكس الحركة أو إرجاع الخامات.',
                ]);
            }
        }

        DB::transaction(function() use ($material, $invoice) {
            $material->delete();

            // If it belonged to an invoice, check if it's the last material
            if ($invoice) {
                if ($invoice->materials()->count() === 0) {
                    $invoice->delete();
                } else {
                    // Update invoice total amount
                    $invoice->total_amount = $invoice->materials()->get()->sum(fn($m) => $m->qty * $m->unit_price);
                    // Pro-rate paid amount down if it exceeds the new total
                    if ($invoice->paid_amount > $invoice->total_amount) {
                        $invoice->paid_amount = $invoice->total_amount;
                    }
                    $invoice->save(); // This triggers MaterialInvoiceObserver to sync debt and transaction
                }
            }
        });

        return back()->with('success', 'تم حذف الصنف بنجاح وتحديث التكاليف والديون المتعلقة به.');
    }
}
