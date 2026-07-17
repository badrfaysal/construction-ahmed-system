<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaborController extends Controller
{
    // Flat list of every work record (band) across all projects — wages and dues per technician/team ل
    public function index(Request $request)
    {
        $query = ProjectBand::with('project')
            ->orderByDesc('labor_date')
            ->orderByDesc('id');

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        $bands    = $query->paginate(60);
        $projects = Project::orderBy('name')->get(['id', 'name']);

        return view('labor.index', compact('bands', 'projects'));
    }

    // Show form to register a new piece of work (picks the project up front)
    public function create()
    {
        $projects = Project::orderBy('name')->get(['id', 'name']);
        
        $knownWorkersJson = \App\Models\BandWorker::select('name', 'phone', 'specialty')
            ->groupBy('name', 'phone', 'specialty')
            ->get()->unique('name')->values()->toJson();

        return view('labor.create', compact('projects', 'knownWorkersJson'));
    }

    // Save the new work record under the chosen project
    public function store(Request $request)
    {
        // An untouched blank worker row would otherwise fail "name required"
        // and silently 422 the whole save — see ProjectBandController for the
        // same fix and full explanation.
        $workers = collect($request->input('workers', []))
            ->filter(fn ($w) => trim($w['name'] ?? '') !== '')
            ->values()
            ->all();
        $request->merge(['workers' => $workers]);

        $data = $request->validate([
            'project_id'    => ['required', 'exists:sy2_projects,id'],
            'name'          => ['required', 'string', 'max:255'],
            'client_price'  => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'in:pending,active,done'],
            // Every band's labor is a list of technicians (no more band-level simple path)
            'workers'                      => ['nullable', 'array'],
            'workers.*.id'                 => ['nullable', 'integer'],
            'workers.*.name'               => ['required', 'string', 'max:255'],
            'workers.*.phone'              => ['nullable', 'string', 'max:30'],
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
        ]);

        $project = Project::findOrFail($data['project_id']);
        $workers = $data['workers'] ?? [];
        unset($data['project_id'], $data['workers']);

        // New bands go to the end of that project's list
        $data['sort_order'] = ($project->bands()->max('sort_order') ?? 0) + 1;
        DB::transaction(function () use ($project, $data, $workers) {
            $band = $project->bands()->create($data);
            $band->syncLabor($workers);
        });

        return redirect()->route('labor.index')
            ->with('success', 'تم تسجيل العمل بنجاح.');
    }
}
