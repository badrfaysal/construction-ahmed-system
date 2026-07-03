<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Warranty;
use App\Models\WarrantyComplaint;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    // List all warranties with their active/expired state
    public function index()
    {
        $warranties = Warranty::with(['project.client', 'complaints'])
            ->orderByDesc('start_date')
            ->get();

        return view('warranties.index', compact('warranties'));
    }

    // Show form to start a warranty for a finished project that doesn't have one yet
    public function create()
    {
        // Only done projects without an existing warranty can start one
        $projects = Project::where('status', 'done')
            ->whereDoesntHave('warranty')
            ->orderBy('name')
            ->get();

        return view('warranties.create', compact('projects'));
    }

    // Save a new warranty record
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:sy2_projects,id', 'unique:sy2_warranties,project_id'],
            'start_date' => ['required', 'date'],
            'months'     => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $warranty = Warranty::create($data);

        return redirect()->route('warranties.show', $warranty->project)
            ->with('success', 'تم بدء الضمان بنجاح.');
    }

    // Show one project's warranty with its complaint history
    public function show(Project $project)
    {
        $project->load('warranty.complaints');

        return view('warranties.show', compact('project'));
    }

    // Log a new complaint during the warranty period
    public function storeComplaint(Request $request, Warranty $warranty)
    {
        $data = $request->validate([
            'date'        => ['required', 'date'],
            'description' => ['required', 'string'],
        ]);

        $data['status'] = 'pending';
        $warranty->complaints()->create($data);

        return back()->with('success', 'تم تسجيل الشكوى.');
    }

    // Mark a complaint as resolved
    public function resolveComplaint(WarrantyComplaint $complaint)
    {
        $complaint->update(['status' => 'resolved']);

        return back()->with('success', 'تم إغلاق الشكوى.');
    }
}
