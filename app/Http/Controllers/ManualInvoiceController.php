<?php

namespace App\Http\Controllers;

use App\Models\ManualInvoice;
use App\Models\ManualInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = ManualInvoice::withCount('items')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('client')) {
            $query->where('client_name', 'like', '%' . $request->client . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $invoices = $query->paginate(25)->withQueryString();

        // Summary totals
        $totals = [
            'count' => ManualInvoice::count(),
            'total' => (float) ManualInvoice::sum(DB::raw('total + tax_amount')),
        ];

        // Stats for sidebar
        $lastInvoice = ManualInvoice::latest('id')->first();
        $topClient = ManualInvoice::select('client_name', DB::raw('count(*) as count'), DB::raw('sum(total + tax_amount) as total_sum'))
            ->groupBy('client_name')
            ->orderByDesc('count')
            ->first();
        $taxInvoicesCount = ManualInvoice::where('tax_amount', '>', 0)->count();

        $stats = [
            'last_client' => $lastInvoice ? $lastInvoice->client_name : null,
            'top_client' => $topClient ? $topClient->client_name : null,
            'top_client_count' => $topClient ? $topClient->count : 0,
            'top_client_total' => $topClient ? $topClient->total_sum : 0,
            'tax_invoices' => $taxInvoicesCount,
        ];

        return view('manual-invoices.index', compact('invoices', 'totals', 'stats'));
    }

    public function create(Request $request)
    {
        $nextNumber = ManualInvoice::nextNumber();
        $recentInvoices = ManualInvoice::orderByDesc('id')->limit(50)->get(['id', 'invoice_number', 'client_name', 'date']);
        $recentMaterialInvoices = \App\Models\MaterialInvoice::orderByDesc('id')->limit(50)->get(['id', 'name', 'date']);
        
        $invoice = null;
        $isDuplicate = false;

        if ($request->filled('copy_from_manual')) {
            $source = ManualInvoice::with('items')->find($request->copy_from_manual);
            if ($source) {
                $invoice = new ManualInvoice();
                // Copy discount/tax/notes, but NOT client data
                $invoice->discount = $source->discount;
                $invoice->tax_pct = $source->tax_pct;
                $invoice->notes = $source->notes;
                $invoice->setRelation('items', $source->items);
                $isDuplicate = true;
            }
        } elseif ($request->filled('copy_from_material')) {
            $source = \App\Models\MaterialInvoice::with('materials')->find($request->copy_from_material);
            if ($source) {
                $invoice = new ManualInvoice();
                $invoice->notes = $source->notes;
                
                $items = $source->materials->map(function($m) {
                    $item = new ManualInvoiceItem();
                    $item->date = $m->date;
                    $item->description = $m->item;
                    $item->qty = $m->qty ?: 1;
                    $item->unit = $m->unit;
                    $price = $m->sell_price ?: $m->unit_price;
                    $item->unit_price = $price;
                    $item->total = $item->qty * $price;
                    return $item;
                });
                $invoice->setRelation('items', $items);
                $isDuplicate = true;
            }
        }

        return view('manual-invoices.form', [
            'invoice' => $invoice,
            'nextNumber' => $nextNumber,
            'recentInvoices' => $recentInvoices,
            'recentMaterialInvoices' => $recentMaterialInvoices,
            'isDuplicate' => $isDuplicate,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $invoice = ManualInvoice::create([
                'invoice_number' => ManualInvoice::nextNumber(),
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'client_address' => $request->client_address,
                'date' => $request->date,
                'discount' => (float) ($request->discount ?? 0),
                'tax_pct' => (float) ($request->tax_pct ?? 0),
                'paid_amount' => (float) ($request->paid_amount ?? 0),
                'notes' => $request->notes,
                'status' => $request->status ?? 'draft',
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['qty'];
                $unitPrice = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'date' => $itemData['date'] ?? null,
                    'description' => $itemData['description'],
                    'qty' => $qty,
                    'unit' => $itemData['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ]);
            }

            $discount = (float) ($request->discount ?? 0);
            $afterDiscount = $subtotal - $discount;
            $taxPct = (float) ($request->tax_pct ?? 0);
            $taxAmount = round($afterDiscount * ($taxPct / 100), 2);
            $total = $afterDiscount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            return redirect()->route('manual_invoices.show', $invoice)
                ->with('success', 'تم حفظ الفاتورة بنجاح');
        });
    }

    public function show(ManualInvoice $invoice)
    {
        $invoice->load('items');
        return view('manual-invoices.show', compact('invoice'));
    }

    public function edit(ManualInvoice $invoice)
    {
        $invoice->load('items');
        return view('manual-invoices.form', [
            'invoice' => $invoice,
            'nextNumber' => $invoice->invoice_number,
        ]);
    }

    public function update(Request $request, ManualInvoice $invoice)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'client_address' => $request->client_address,
                'date' => $request->date,
                'discount' => (float) ($request->discount ?? 0),
                'tax_pct' => (float) ($request->tax_pct ?? 0),
                'paid_amount' => (float) ($request->paid_amount ?? 0),
                'notes' => $request->notes,
                'status' => $request->status ?? 'draft',
            ]);

            // Delete old items and re-create
            $invoice->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['qty'];
                $unitPrice = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'date' => $itemData['date'] ?? null,
                    'description' => $itemData['description'],
                    'qty' => $qty,
                    'unit' => $itemData['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ]);
            }

            $discount = (float) ($request->discount ?? 0);
            $afterDiscount = $subtotal - $discount;
            $taxPct = (float) ($request->tax_pct ?? 0);
            $taxAmount = round($afterDiscount * ($taxPct / 100), 2);
            $total = $afterDiscount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            return redirect()->route('manual_invoices.show', $invoice)
                ->with('success', 'تم تحديث الفاتورة بنجاح');
        });
    }

    public function destroy(ManualInvoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('manual_invoices.index')
            ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    // JSON endpoint for autocomplete
    public function autocomplete(Request $request)
    {
        $type = $request->get('type');
        $q = $request->get('q', '');

        return match ($type) {
            'client' => \App\Models\Client::where('name', 'like', "%{$q}%")
                ->limit(10)
                ->pluck('name'),

            'item' => DB::table('sy2_materials')
                ->select('item')
                ->where('item', 'like', "%{$q}%")
                ->groupBy('item')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(15)
                ->pluck('item'),

            'unit' => DB::table('sy2_materials')
                ->select('unit')
                ->whereNotNull('unit')
                ->where('unit', '!=', '')
                ->where('unit', 'like', "%{$q}%")
                ->groupBy('unit')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->pluck('unit'),

            'client_phone' => \App\Models\Client::where('name', $q)
                ->whereNotNull('phone')
                ->limit(1)
                ->pluck('phone'),

            'client_address' => \App\Models\Client::where('name', $q)
                ->whereNotNull('address')
                ->limit(1)
                ->pluck('address'),

            default => [],
        };
    }
}
