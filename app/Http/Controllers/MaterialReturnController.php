<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MaterialReturnController extends Controller
{
    // Show form to add a manual return against one specific purchase in this project
    public function create(Project $project)
    {
        $materials = $project->materials()
            ->where(function($q) {
                $q->whereNull('category')->orWhere('category', '!=', 'misc');
            })
            ->with('returns')
            ->orderByDesc('date')
            ->get();

        return view('returns.create', compact('project', 'materials'));
    }

    // Save one or more returns in a single submission — each row's qty can
    // never exceed what's still net-remaining on its purchase.
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'date'                    => ['required', 'date'],
            'notes'                   => ['nullable', 'string'],
            'returns'                 => ['required', 'array', 'min:1'],
            'returns.*.material_id'   => ['required', 'exists:sy2_materials,id'],
            'returns.*.qty'           => ['required', 'numeric', 'min:0.01'],
            'returns.*.return_price'  => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($project->hasWholeProjectInstallmentContract()) {
            throw ValidationException::withMessages([
                'date' => 'لا يمكن عمل مرتجع لمشروع له عقد تقسيط بالكامل. يجب إلغاء عقد التقسيط أولاً.',
            ]);
        }

        // Validate every row up-front so nothing is saved if any row over-returns
        $rows = [];
        foreach ($data['returns'] as $i => $row) {
            $material = Material::where('project_id', $project->id)->findOrFail($row['material_id']);
            
            if ($material->band_id) {
                $band = \App\Models\ProjectBand::find($material->band_id);
                if ($band && $band->hasInstallmentContract()) {
                    throw ValidationException::withMessages([
                        "returns.$i.qty" => 'هذا البند له عقد تقسيط ولا يمكن عمل مرتجع لخاماته. قم بإلغاء التقسيط أولاً.',
                    ]);
                }
            }

            if ($material->isMisc()) {
                throw ValidationException::withMessages([
                    "returns.$i.qty" => 'لا يمكن عمل مرتجع للنثريات (' . $material->item . ').',
                ]);
            }
            if ($row['qty'] > $material->netQty()) {
                throw ValidationException::withMessages([
                    "returns.$i.qty" => 'الكمية المرتجعة لـ"' . $material->item . '" أكبر من الصافي المتبقي (' . $material->netQty() . ').',
                ]);
            }
            $rows[] = ['material' => $material, 'qty' => $row['qty'], 'return_price' => $row['return_price'] ?? null];
        }

        // Creating each return fires MaterialReturnObserver, which re-syncs the
        // purchase's سجل الحركات amount and credits محفظة المقاولات back.
        DB::transaction(function () use ($rows, $data) {
            foreach ($rows as $r) {
                MaterialReturn::create([
                    'material_id'  => $r['material']->id,
                    'qty'          => $r['qty'],
                    'return_price' => $r['return_price'],
                    'date'         => $data['date'],
                    'notes'        => $data['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تسجيل المرتجعات بنجاح.');
    }

    // Delete a return (e.g. entered by mistake) — MaterialReturnObserver raises
    // the purchase's net cost back up, so it's blocked if محفظة المقاولات can no
    // longer cover it. أدمن فقط + باسورد — مرتجع على شراء آجل بالكامل مالوش
    // حركة في سجل الحركات أصلاً (مفيش مبلغ اترد فعليًا)، فمفيش طريقة تانية
    // تحذفه غير من هنا مباشرة.
    public function destroy(Request $request, MaterialReturn $return)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate(['current_password' => ['required', 'string']]);
        if (! Hash::check($data['current_password'], auth()->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'كلمة مرور الأدمن غير صحيحة.']);
        }

        $project = $return->material->project;

        DB::transaction(fn () => $return->delete());

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم حذف المرتجع.');
    }
}
