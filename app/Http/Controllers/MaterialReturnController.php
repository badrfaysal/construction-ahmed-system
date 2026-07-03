<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialReturnController extends Controller
{
    // Show form to add a manual return against one specific purchase in this project
    public function create(Project $project)
    {
        $materials = $project->materials()->with('returns')->orderByDesc('date')->get();

        return view('returns.create', compact('project', 'materials'));
    }

    // Save one or more returns in a single submission — each row's qty can
    // never exceed what's still net-remaining on its purchase.
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'date'                 => ['required', 'date'],
            'notes'                => ['nullable', 'string'],
            'returns'              => ['required', 'array', 'min:1'],
            'returns.*.material_id'=> ['required', 'exists:sy2_materials,id'],
            'returns.*.qty'        => ['required', 'numeric', 'min:0.01'],
        ]);

        // Validate every row up-front so nothing is saved if any row over-returns
        $rows = [];
        foreach ($data['returns'] as $i => $row) {
            $material = Material::where('project_id', $project->id)->findOrFail($row['material_id']);
            if ($row['qty'] > $material->netQty()) {
                throw ValidationException::withMessages([
                    "returns.$i.qty" => 'الكمية المرتجعة لـ"' . $material->item . '" أكبر من الصافي المتبقي (' . $material->netQty() . ').',
                ]);
            }
            $rows[] = ['material' => $material, 'qty' => $row['qty']];
        }

        // Creating each return fires MaterialReturnObserver, which re-syncs the
        // purchase's سجل الحركات amount and credits محفظة المقاولات back.
        DB::transaction(function () use ($rows, $data) {
            foreach ($rows as $r) {
                MaterialReturn::create([
                    'material_id' => $r['material']->id,
                    'qty'         => $r['qty'],
                    'date'        => $data['date'],
                    'notes'       => $data['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تسجيل المرتجعات بنجاح.');
    }

    // Delete a return (e.g. entered by mistake) — MaterialReturnObserver raises
    // the purchase's net cost back up, so it's blocked if محفظة المقاولات can no
    // longer cover it.
    public function destroy(MaterialReturn $return)
    {
        $project = $return->material->project;

        DB::transaction(fn () => $return->delete());

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم حذف المرتجع.');
    }
}
