<?php

namespace App\Http\Controllers;

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

        $projects = Project::with(['client', 'bands.materials.returns', 'installments'])
            ->where('status', $tab === 'done' ? 'done' : 'active')
            ->orderByDesc('created_at')
            ->get();

        $activeCnt = Project::where('status', 'active')->count();
        $doneCnt   = Project::where('status', 'done')->count();

        return view('projects.index', compact('projects', 'tab', 'activeCnt', 'doneCnt'));
    }

    // Show form to create a new project
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('projects.create', compact('clients'));
    }

    // Save the new project
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'    => ['required', 'exists:sy2_clients,id'],
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string'],
            'area'         => ['nullable', 'numeric', 'min:0'],
            'default_supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date'   => ['nullable', 'date'],
            'deliver_date' => ['nullable', 'date'],
            'notes'        => ['nullable', 'string'],
        ]);

        $project = Project::create($data);

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
            'warranty',
        ]);

        return view('projects.show', compact('project'));
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
            'default_supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date'     => ['nullable', 'date'],
            'deliver_date'   => ['nullable', 'date'],
            'delivered_date' => ['nullable', 'date'],
            'status'         => ['required', 'in:active,done'],
            'notes'          => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تحديث المشروع.');
    }

    // Delete a project (and cascade to bands, materials, installments)
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', 'تم حذف المشروع.');
    }
}
