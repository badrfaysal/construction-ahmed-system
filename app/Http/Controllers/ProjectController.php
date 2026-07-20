<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectBand;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // List all projects with tabs: active / done
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active'); // default to active tab

        $validTabs = ['active', 'done', 'suspended', 'canceled'];
        $status = in_array($tab, $validTabs) ? $tab : 'active';

        $projects = Project::with(['client', 'bands', 'contracts', 'discounts'])
            ->withSum(['transactions as total_worker_paid' => function ($query) {
                $query->where('ref_type', 'worker_payment');
            }], 'amount')
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        $activeCnt    = Project::where('status', 'active')->count();
        $doneCnt      = Project::where('status', 'done')->count();
        $suspendedCnt = Project::where('status', 'suspended')->count();
        $canceledCnt  = Project::where('status', 'canceled')->count();

        return view('projects.index', compact('projects', 'tab', 'activeCnt', 'doneCnt', 'suspendedCnt', 'canceledCnt'));
    }

    // Show form to create a new project
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $existingProjects = Project::orderBy('name')->get(['id', 'name']);
        return view('projects.create', compact('clients', 'existingProjects'));
    }

    // Save the new project
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'    => ['required', 'exists:sy2_clients,id'],
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string'],
            'area'         => ['nullable', 'numeric', 'min:0'],
            'default_supervision_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date'   => ['nullable', 'date'],
            'deliver_date' => ['nullable', 'date'],
            'notes'        => ['nullable', 'string'],
            'copy_project_id' => ['nullable', 'exists:sy2_projects,id'],
        ]);

        $copyProjectId = $data['copy_project_id'] ?? null;
        unset($data['copy_project_id']);

        $project = Project::create($data);

        if ($copyProjectId) {
            $sourceProject = Project::with('bands.workers')->find($copyProjectId);
            if ($sourceProject) {
                foreach ($sourceProject->bands as $sourceBand) {
                    $newBand = $project->bands()->create([
                        'name'                  => $sourceBand->name,
                        'client_price'          => $sourceBand->client_price,
                        'status'                => 'active',
                        'contract_type'         => $sourceBand->contract_type,
                        'contract_qty'          => $sourceBand->contract_qty,
                        'contract_unit_rate'    => $sourceBand->contract_unit_rate,
                        'labor_sell_rate'       => $sourceBand->labor_sell_rate,
                        'team_name'             => $sourceBand->team_name,
                        'labor_amount'          => $sourceBand->labor_amount,
                        'labor_sell_price'      => $sourceBand->labor_sell_price,
                        'labor_supervision_pct' => $sourceBand->labor_supervision_pct,
                        'sort_order'            => $sourceBand->sort_order,
                    ]);

                    foreach ($sourceBand->workers as $sourceWorker) {
                        $newBand->workers()->create([
                            'name'               => $sourceWorker->name,
                            'phone'              => $sourceWorker->phone,
                            'specialty'          => $sourceWorker->specialty,
                            'contract_type'      => $sourceWorker->contract_type,
                            'contract_qty'       => $sourceWorker->contract_qty,
                            'contract_unit_rate' => $sourceWorker->contract_unit_rate,
                            'sell_rate'          => $sourceWorker->sell_rate,
                            'amount'             => $sourceWorker->amount,
                            'sell_amount'        => $sourceWorker->sell_amount,
                            'supervision_pct'    => $sourceWorker->supervision_pct,
                            'notes'              => $sourceWorker->notes,
                            'sort_order'         => $sourceWorker->sort_order,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم إنشاء المشروع بنجاح.');
    }

    // Show a single project with all its bands, materials, installments
    public function show(Project $project)
    {
        // Load everything needed to avoid separate queries in the view
        $project->load([
            'client',
            'bands.materials.supplier',
            'bands.materials.returns',
            'bands.workers.payments',
            'materials.returns',
            'installments',
            'transactions',
            'contracts.payments',
            'warranty',
        ]);

        $wallets = Account::selectable();
        $marketers = \App\Models\Marketer::orderBy('name')->get();

        // Chart & Metrics Data
        $collected = $project->totalCollected();
        $remaining = max($project->amountDue(), 0);

        $generalMaterialsCost = $project->generalMaterials()->sum(fn ($m) => $m->netCost());
        $allMaterialsCost = $project->materials->sum(fn ($m) => $m->netCost());
        $bandMaterialsCost = $allMaterialsCost - $generalMaterialsCost;
        
        $laborCost = $project->bands->sum('labor_amount');
        $marketersCost = (float) $project->transactions()->where('ref_type', 'marketer_commission')->sum('amount');
        
        $projectMarketers = $project->transactions()
            ->where('ref_type', 'marketer_commission')
            ->get()
            ->groupBy('ref_id')
            ->map(function ($txs) {
                $marketer = \App\Models\Marketer::find($txs->first()->ref_id);
                return (object)[
                    'name' => $marketer ? $marketer->name : 'غير معروف',
                    'total' => $txs->sum('amount')
                ];
            })
            ->values();

        $totalCost = $project->totalSpent();

        return view('projects.show', compact('project', 'wallets', 'marketers', 
            'collected', 'remaining', 'generalMaterialsCost', 'bandMaterialsCost', 
            'laborCost', 'marketersCost', 'projectMarketers', 'totalCost'));
    }

    // Show edit form
    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get();
        return view('projects.edit', compact('project', 'clients'));
    }

    // Save edits
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'client_id'      => ['required', 'exists:sy2_clients,id'],
            'name'           => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'area'           => ['nullable', 'numeric', 'min:0'],
            'default_supervision_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date'     => ['nullable', 'date'],
            'deliver_date'   => ['nullable', 'date'],
            'delivered_date' => ['nullable', 'date'],
            'status'         => ['required', 'in:active,done,suspended,canceled'],
            'notes'          => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تحديث المشروع.');
    }

    public function changeStatus(Request $request, Project $project)
    {
        $request->validate([
            'status' => ['required', 'in:active,done,suspended,canceled']
        ]);

        $project->update(['status' => $request->status]);

        return back()->with('success', 'تم تغيير حالة المشروع بنجاح.');
    }

    public function payCommission(Request $request, Project $project)
    {
        $data = $request->validate([
            'marketer_id' => ['required', 'exists:sy2_marketers,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'date'        => ['required', 'date'],
            'account_id'  => ['required', 'exists:accounts,id'],
            'notes'       => ['nullable', 'string'],
        ]);

        $project->transactions()->create([
            'account_id'  => $data['account_id'],
            'direction'   => 'out',
            'type'        => 'expense',
            'party'       => \App\Models\Marketer::find($data['marketer_id'])->name,
            'amount'      => $data['amount'],
            'date'        => $data['date'],
            'description' => 'عمولة تسويق' . ($data['notes'] ? ' — ' . $data['notes'] : ''),
            'ref_type'    => 'marketer_commission',
            'ref_id'      => $data['marketer_id'],
        ]);

        return back()->with('success', 'تم تسجيل العمولة بنجاح وخصمها من المشروع والمحفظة.');
    }

    // Delete a project (and cascade to bands, materials, installments)
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', 'تم حذف المشروع.');
    }
}
