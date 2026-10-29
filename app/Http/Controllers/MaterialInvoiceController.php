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
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
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

    public function edit(MaterialInvoice $invoice)
    {
        $invoice->load(['project.bands', 'supplier', 'account', 'materials.returns']);
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $accounts = \App\Models\Account::orderBy('name')->get();
        $knownItems = \App\Http\Controllers\MaterialController::knownItemNames();
        $knownUnits = \App\Http\Controllers\MaterialController::knownUnitNames();
        
        return view('material_invoices.edit', compact('invoice', 'suppliers', 'accounts', 'knownItems', 'knownUnits'));
    }

    public function update(Request $request, MaterialInvoice $invoice)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:sy2_material_invoices,name,' . $invoice->id],
            'date'           => ['required', 'date'],
            'supplier_id'    => ['required', 'exists:sy2_suppliers,id'],
            'payment_status' => ['required', 'in:paid,partial,deferred'],
            'account_id'     => ['required_unless:payment_status,deferred', 'nullable', 'integer', 'exists:sy2_accounts,id'],
            'paid_amount'    => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.id'     => ['nullable', 'integer', 'exists:sy2_materials,id'],
            'items.*.band_id'=> ['nullable', 'exists:sy2_project_bands,id'],
            'items.*.item'   => ['required', 'string', 'max:255'],
            'items.*.unit'   => ['required', 'string', 'max:50'],
            'items.*.qty'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.sell_price' => ['required', 'numeric', 'min:0'],
            'items.*.supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $totalCost = 0;
        foreach ($data['items'] as $item) {
            $totalCost += (float)$item['qty'] * (float)$item['unit_price'];
        }

        $paymentStatus = $data['payment_status'];
        $paidAmount = (float)($data['paid_amount'] ?? 0);

        if ($paymentStatus === 'paid') {
            $paidAmount = $totalCost;
        } elseif ($paymentStatus === 'deferred') {
            $paidAmount = 0;
        } elseif ($paymentStatus === 'partial' && $paidAmount > $totalCost + 0.01) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'paid_amount' => 'المبلغ المدفوع أكبر من إجمالي تكلفة الفاتورة (' . number_format($totalCost, 2) . ' ج.م).',
            ]);
        }

        $existingMaterials = $invoice->materials()->with('returns')->get()->keyBy('id');
        $submittedItemIds = collect($data['items'])->pluck('id')->filter()->map(fn($v) => (int)$v)->all();

        // Check if any deleted item had returns
        foreach ($existingMaterials as $existingId => $existingMat) {
            if (!in_array($existingId, $submittedItemIds) && $existingMat->returnedQty() > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'لا يمكن حذف الصنف "' . $existingMat->item . '" لوجود مرتجعات مسجلة عليه.',
                ]);
            }
        }

        // Check if any item quantity is reduced below returned quantity
        foreach ($data['items'] as $itemData) {
            if (!empty($itemData['id']) && isset($existingMaterials[$itemData['id']])) {
                $mat = $existingMaterials[$itemData['id']];
                if ((float)$itemData['qty'] < $mat->returnedQty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'الكمية الجديدة للصنف "' . $mat->item . '" (' . $itemData['qty'] . ') أقل من الكمية المرتجعة بالفعل (' . $mat->returnedQty() . ').',
                    ]);
                }
            }
        }

        $bandsToRecalculate = $existingMaterials->pluck('band_id')->filter()->unique();

        DB::transaction(function() use ($invoice, $data, $totalCost, $paidAmount, $paymentStatus, $existingMaterials, $submittedItemIds, &$bandsToRecalculate) {
            // 1. Update invoice fields
            $invoice->update([
                'name'         => $data['name'],
                'date'         => $data['date'],
                'supplier_id'  => $data['supplier_id'],
                'account_id'   => $paymentStatus === 'deferred' ? null : ($data['account_id'] ?? null),
                'total_amount' => $totalCost,
                'paid_amount'  => $paidAmount,
                'notes'        => $data['notes'] ?? null,
            ]);

            // 2. Delete removed items
            foreach ($existingMaterials as $existingId => $existingMat) {
                if (!in_array($existingId, $submittedItemIds)) {
                    $existingMat->delete();
                }
            }

            // 3. Update existing or create new items
            foreach ($data['items'] as $itemData) {
                $itemCost = (float)$itemData['qty'] * (float)$itemData['unit_price'];
                $itemPaid = $paymentStatus === 'paid' ? $itemCost : ($totalCost > 0 ? ($paidAmount * ($itemCost / $totalCost)) : 0);

                if (!empty($itemData['band_id'])) {
                    $bandsToRecalculate->push((int)$itemData['band_id']);
                }

                $fields = [
                    'project_id'      => $invoice->project_id,
                    'band_id'         => $itemData['band_id'] ?? null,
                    'account_id'      => $invoice->account_id,
                    'supplier_id'     => $invoice->supplier_id,
                    'item'            => $itemData['item'],
                    'unit'            => $itemData['unit'],
                    'qty'             => $itemData['qty'],
                    'unit_price'      => $itemData['unit_price'],
                    'sell_price'      => $itemData['sell_price'],
                    'supervision_pct' => $itemData['supervision_pct'] ?? 0,
                    'date'            => $invoice->date,
                    'payment_status'  => $paymentStatus,
                    'paid_amount'     => $itemPaid,
                ];

                if (!empty($itemData['id']) && isset($existingMaterials[$itemData['id']])) {
                    $existingMaterials[$itemData['id']]->update($fields);
                } else {
                    $fields['invoice_id'] = $invoice->id;
                    $fields['category'] = 'misc';
                    \App\Models\Material::create($fields);
                }
            }

            // Touch invoice to trigger observer sync
            $invoice->touch();
        });

        // Recalculate cached totals
        foreach (\App\Models\ProjectBand::whereIn('id', $bandsToRecalculate->unique())->get() as $band) {
            $band->recalculateCachedTotals();
        }
        $invoice->project?->recalculateCachedTotals();

        return redirect()->route('material_invoices.show', $invoice->id)
            ->with('success', 'تم تعديل الفاتورة وتحديث التكاليف والديون وسجل الحركات بنجاح.');
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
