<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Supplier;
use App\Support\ItemNameMatcher;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // List all suppliers with aggregated stats from materials
    public function index(Request $request)
    {
        // Load each supplier with a summary of their total deals
        $suppliers = Supplier::withCount('materials')
            ->with('materials.returns')
            ->orderBy('name')
            ->get();

        // Calculate totals per supplier from materials
        $suppliers = $suppliers->map(function ($s) {
            $s->total_spent   = $s->materials->sum(fn ($m) => $m->netCost());
            $s->total_returns = $s->materials->sum(fn ($m) => $m->returnedQty() * $m->unit_price);
            return $s;
        });

        $suppliers = (match ($request->get('sort', 'name')) {
            'newest'      => $suppliers->sortByDesc('id'),
            'oldest'      => $suppliers->sortBy('id'),
            'spent_desc'  => $suppliers->sortByDesc('total_spent'),
            'spent_asc'   => $suppliers->sortBy('total_spent'),
            default       => $suppliers->sortBy('name'),
        })->values();

        return view('suppliers.index', compact('suppliers'));
    }

    // Show one supplier with all their transactions
    public function show(Supplier $supplier)
    {
        $supplier->load(['materials.project', 'materials.band', 'materials.returns']);
        $supplier->total_spent   = $supplier->materials->sum(fn ($m) => $m->netCost());
        $supplier->total_returns = $supplier->materials->sum(fn ($m) => $m->returnedQty() * $m->unit_price);

        return view('suppliers.show', compact('supplier'));
    }

    // Show create form
    public function create()
    {
        $activities = $this->existingActivities();
        return view('suppliers.create', compact('activities'));
    }

    // Distinct activities already entered — feeds the autocomplete datalist
    private function existingActivities()
    {
        return Supplier::whereNotNull('activity')->where('activity', '!=', '')
            ->distinct()->orderBy('activity')->pluck('activity');
    }

    // Save new supplier
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30', new \App\Rules\UniquePhone('sy2_suppliers')],
            'email'    => ['nullable', 'email'],
            'address'  => ['nullable', 'string'],
            'notes'    => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم إضافة المورد.');
    }

    // Compare prices across suppliers for the same material item —
    // helps pick the cheapest supplier next time that item is needed
    public function compare()
    {
        $materials = Material::with('supplier')->whereNotNull('supplier_id')->get();

        // Fuzzy-grouped by item name so "جردل بوية" / "جردل البويه" compare
        // together instead of being treated as separate items (ItemNameMatcher)
        $groups = ItemNameMatcher::group($materials, fn ($m) => $m->item);

        $comparison = collect($groups)
            ->map(function ($g) {
                $bySupplier = collect($g['items'])->groupBy('supplier_id')
                    ->map(fn ($group) => (object) [
                        'supplier'  => $group->first()->supplier,
                        'avg_price' => $group->avg('unit_price'),
                        'unit'      => $group->first()->unit,
                    ])
                    ->sortBy('avg_price')
                    ->values();

                return (object) ['item' => $g['canonical'], 'variants' => $g['variants'], 'suppliers' => $bySupplier];
            })
            // Only items quoted by more than one supplier are worth comparing
            ->filter(fn ($row) => $row->suppliers->count() > 1)
            ->values();

        return view('suppliers.compare', compact('comparison'));
    }

    // Show edit form
    public function edit(Supplier $supplier)
    {
        $activities = $this->existingActivities();
        return view('suppliers.edit', compact('supplier', 'activities'));
    }

    // Save edits
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30', new \App\Rules\UniquePhone('sy2_suppliers', $supplier->id)],
            'email'    => ['nullable', 'email'],
            'address'  => ['nullable', 'string'],
            'notes'    => ['nullable', 'string'],
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم تحديث بيانات المورد.');
    }
}
