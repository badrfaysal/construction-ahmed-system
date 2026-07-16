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
