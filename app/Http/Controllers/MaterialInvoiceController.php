<?php

namespace App\Http\Controllers;

use App\Models\MaterialInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = clone MaterialInvoice::with(['project', 'supplier'])->latest('date')->latest('id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->paginate(20)->withQueryString();
        $projects = \App\Models\Project::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('material_invoices.index', compact('invoices', 'projects', 'suppliers'));
    }

    public function show(MaterialInvoice $invoice)
    {
        $invoice->load(['project', 'supplier', 'account', 'materials.band']);
        return view('material_invoices.show', compact('invoice'));
    }

    public function destroy(MaterialInvoice $invoice)
    {
        $projectId = $invoice->project_id;
        $bandsToRecalculate = $invoice->materials->pluck('band_id')->filter()->unique();
        
        DB::transaction(function() use ($invoice) {
            // Delete materials first, so observers can fire if needed 
            // (though we bypassed most logic in MaterialObserver when invoice_id exists)
            $invoice->materials()->delete();
            // Delete the invoice, triggering MaterialInvoiceObserver to delete Transactions and Debts
            $invoice->delete();
        });

        // Recalculate financial totals for affected bands and the project
        foreach (\App\Models\ProjectBand::whereIn('id', $bandsToRecalculate)->get() as $band) {
            $band->recalculateCachedTotals();
        }
        \App\Models\Project::find($projectId)?->recalculateCachedTotals();

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'تم حذف الفاتورة وكل الخامات التابعة لها بنجاح.');
    }
}
