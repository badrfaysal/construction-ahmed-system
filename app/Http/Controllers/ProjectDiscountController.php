<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectDiscountController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date'   => 'required|date',
            'notes'  => 'nullable|string|max:255',
        ]);

        if ($project->hasWholeProjectInstallmentContract()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'لا يمكن إضافة خصم إجمالي للمشروع لأنه يحتوي على عقد تقسيط إجمالي. قم بإلغاء التقسيط أولاً.',
            ]);
        }

        $contractedBandsBilled = 0;
        foreach ($project->bands as $b) {
            if ($project->contracts()->where('band_id', $b->id)->exists()) {
                $contractedBandsBilled += $b->actualClientTotal();
            }
        }
        $uncontractedBilled = $project->actualClientTotal() - $contractedBandsBilled;

        if ($request->amount > $uncontractedBilled) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'مبلغ الخصم (' . $request->amount . ') أكبر من القيمة المتبقية غير المقسطة من المشروع (' . number_format(max(0, $uncontractedBilled), 2) . ' ج).',
            ]);
        }

        $project->discounts()->create([
            'amount' => $request->amount,
            'date'   => $request->date,
            'notes'  => $request->notes,
        ]);

        // Recalculate and update the cached values
        $project->recalculateCachedTotals();

        return back()->with('success', 'تم إضافة الخصم بنجاح.');
    }
}
