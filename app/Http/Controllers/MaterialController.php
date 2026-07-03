<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    // List all materials across all projects, with optional project filter
    public function index(Request $request)
    {
        $query = Material::with(['project', 'band', 'supplier', 'returns'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        $materials = $query->paginate(60);
        $projects  = Project::orderBy('name')->get(['id', 'name']);

        return view('materials.index', compact('materials', 'projects'));
    }

    // Show form to register a batch of purchases: one project, several bands,
    // each band with several items — all saved together in one submission
    public function create(Request $request)
    {
        $projects  = Project::orderBy('name')->get(['id', 'name', 'default_supervision_pct']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        // Pre-select a project if provided via query string (e.g. from a project page)
        $selectedProject = $request->get('project_id') ? Project::find($request->get('project_id')) : null;
        $bands = $selectedProject ? $selectedProject->bands()->get(['id', 'name']) : collect();

        return view('materials.create', compact('projects', 'suppliers', 'bands', 'selectedProject'));
    }

    // Save every item from every band group in one go
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'                    => ['required', 'exists:sy2_projects,id'],
            'groups'                        => ['required', 'array', 'min:1'],
            'groups.*.band_id'              => ['nullable', 'exists:sy2_project_bands,id'],
            'groups.*.supplier_id'          => ['nullable', 'exists:sy2_suppliers,id'],
            'groups.*.date'                 => ['required', 'date'],
            'groups.*.payment_status'       => ['nullable', 'in:paid,partial,deferred'],
            'groups.*.paid_amount'          => ['nullable', 'numeric', 'min:0'],
            'groups.*.items'                => ['required', 'array', 'min:1'],
            'groups.*.items.*.item'         => ['required', 'string', 'max:255'],
            'groups.*.items.*.unit'         => ['required', 'string', 'max:50'],
            'groups.*.items.*.qty'          => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.sell_price'   => ['required', 'numeric', 'min:0'],
            'groups.*.items.*.supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        // Wrapped in one transaction so a purchase that would overdraw
        // محفظة المقاولات rolls back the whole batch, not just that one item.
        $count = DB::transaction(function () use ($data) {
            $count = 0;
            foreach ($data['groups'] as $group) {
                $paymentStatus = $group['payment_status'] ?? 'paid';
                // For partial payment, distribute the paid_amount proportionally across items
                $groupItems = $group['items'];
                $groupTotalCost = array_sum(array_map(
                    fn ($i) => (float)$i['qty'] * (float)$i['unit_price'],
                    $groupItems
                ));
                $groupPaidAmount = (float) ($group['paid_amount'] ?? 0);

                foreach ($groupItems as $item) {
                    $itemCost = (float)$item['qty'] * (float)$item['unit_price'];
                    // Distribute partial payment proportionally across items in the group
                    $itemPaidAmount = $paymentStatus === 'partial' && $groupTotalCost > 0
                        ? round($groupPaidAmount * ($itemCost / $groupTotalCost), 2)
                        : 0;

                    Material::create([
                        'project_id'      => $data['project_id'],
                        'band_id'         => $group['band_id'] ?? null,
                        'supplier_id'     => $group['supplier_id'] ?? null,
                        'item'            => $item['item'],
                        'unit'            => $item['unit'],
                        'qty'             => $item['qty'],
                        'unit_price'      => $item['unit_price'],
                        'sell_price'      => $item['sell_price'],
                        'supervision_pct' => $item['supervision_pct'] ?? 0,
                        'date'            => $group['date'],
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

    // Delete a material entry
    public function destroy(Material $material)
    {
        DB::transaction(fn () => $material->delete());
        return back()->with('success', 'تم حذف الخامة.');
    }

    // Show form to add a miscellaneous expense (نثري) to a project — tips,
    // transport, meals, etc. Defaults the band to the one currently in progress.
    public function createExpense(Project $project)
    {
        $bands       = $project->bands()->orderBy('sort_order')->get(['id', 'name', 'status']);
        $activeBand  = $bands->firstWhere('status', 'active');
        $defaultSup  = $project->defaultSupervisionPct();

        return view('materials.expense', compact('project', 'bands', 'activeBand', 'defaultSup'));
    }

    // Save a misc expense as a Material row (category=misc) so it flows through
    // the wallet, client statement, and cost statement exactly like a purchase.
    public function storeExpense(Request $request, Project $project)
    {
        $data = $request->validate([
            'band_id'         => ['nullable', 'exists:sy2_project_bands,id'],
            'item'            => ['required', 'string', 'max:255'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'sell_price'      => ['required', 'numeric', 'min:0'],
            'supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date'            => ['required', 'date'],
        ]);

        DB::transaction(fn () => Material::create([
            'project_id'      => $project->id,
            'band_id'         => $data['band_id'] ?? null,
            'category'        => 'misc',
            'item'            => $data['item'],
            'unit'            => 'مبلغ',
            'qty'             => 1,
            'unit_price'      => $data['amount'],
            'sell_price'      => $data['sell_price'],
            'supervision_pct' => $data['supervision_pct'] ?? 0,
            'date'            => $data['date'],
        ]));

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تسجيل المصروف النثري.');
    }
}
