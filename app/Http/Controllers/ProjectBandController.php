<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBand;
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

        return view('bands.create', compact('project'));
    }

    // Save a new band under the given project
    public function store(Request $request, Project $project)
    {
        if ($project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'name' => 'تم تقسيط المشروع بالكامل — لا يمكن إضافة بنود جديدة لهذا المشروع.',
            ]);
        }

        $this->stripEmptyWorkers($request);
        $data = $this->validateData($request);
        $workers = $data['workers'] ?? [];
        unset($data['workers']);

        // New bands go to the end of the list
        $data['sort_order'] = ($project->bands()->max('sort_order') ?? 0) + 1;

        DB::transaction(function () use ($project, $data, $workers) {
            $band = $project->bands()->create($data);
            $band->syncLabor($workers);
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم إضافة البند بنجاح.');
    }

    // Show edit form for one band
    public function edit(ProjectBand $band)
    {
        $band->load('workers');
        $legacySeed = $band->legacyWorkerSeed();
        return view('bands.edit', compact('band', 'legacySeed'));
    }

    // Save edits to a band
    public function update(Request $request, ProjectBand $band)
    {
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

    // Shared validation rules for store() and update() — every band's labor
    // is now always a list of technicians (no more band-level simple team/day-rate path)
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'client_price'  => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'in:pending,active,done'],
            'workers'                      => ['nullable', 'array'],
            // id present = update that worker in place (keeps his دفعات) —
            // syncLabor() scopes the lookup to the band's own workers
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
    }
}
