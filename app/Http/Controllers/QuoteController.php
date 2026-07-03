<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Material;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    // List quotes with optional status filter (tab)
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');

        $query = Quote::with('bands.items')->orderByDesc('date');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $quotes   = $query->paginate(30);
        $counts   = Quote::selectRaw("status, count(*) as cnt")->groupBy('status')->pluck('cnt', 'status');

        return view('quotes.index', compact('quotes', 'tab', 'counts'));
    }

    // Show a single quote with its bands
    public function show(Quote $quote)
    {
        $quote->load('bands.items');
        return view('quotes.show', compact('quote'));
    }

    // Show create form
    public function create()
    {
        // Generate the next reference number automatically
        $lastRef = Quote::max('ref');
        $nextNum = $lastRef ? intval(substr($lastRef, -3)) + 1 : 1;
        $nextRef = 'QT-' . now()->year . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $clients = Client::orderBy('name')->get();

        return view('quotes.create', compact('nextRef', 'clients'));
    }

    // Save a new quote with its band line items
    public function store(Request $request)
    {
        $data = $this->validateQuote($request, ['ref' => ['required', 'string', 'max:50', 'unique:sy2_quotes,ref']]);

        $client = Client::findOrFail($data['client_id']);

        $quote = Quote::create([
            ...\Arr::except($data, ['bands']),
            'client_name' => $client->name,
            'phone'       => $client->phone,
        ]);

        $this->saveBands($quote, $data['bands'] ?? []);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'تم إنشاء عرض السعر.');
    }

    // Show edit form — only while the quote is still a draft
    public function edit(Quote $quote)
    {
        abort_unless($quote->status === 'draft', 403, 'لا يمكن تعديل عرض تم إرساله أو اعتماده.');

        $quote->load('bands.items');
        $clients = Client::orderBy('name')->get();

        return view('quotes.edit', compact('quote', 'clients'));
    }

    // Save edits — only while the quote is still a draft
    public function update(Request $request, Quote $quote)
    {
        abort_unless($quote->status === 'draft', 403, 'لا يمكن تعديل عرض تم إرساله أو اعتماده.');

        $data = $this->validateQuote($request, ['ref' => ['required', 'string', 'max:50', 'unique:sy2_quotes,ref,' . $quote->id]]);

        $client = Client::findOrFail($data['client_id']);

        $quote->update([
            ...\Arr::except($data, ['bands']),
            'client_name' => $client->name,
            'phone'       => $client->phone,
        ]);

        // Replace the band/item breakdown entirely with what was submitted
        $quote->bands()->delete();
        $this->saveBands($quote, $data['bands'] ?? []);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'تم تحديث عرض السعر.');
    }

    // Dedicated screen for approved quotes only — ready to be turned into projects/contracts
    public function approved()
    {
        $quotes = Quote::with('bands.items')
            ->where('status', 'approved')
            ->orderByDesc('date')
            ->get();

        $stats = [
            'count'      => $quotes->count(),
            'total_value'=> $quotes->sum(fn ($q) => $q->total()),
            'avg_value'  => $quotes->count() ? $quotes->avg(fn ($q) => $q->total()) : 0,
        ];

        return view('quotes.approved', compact('quotes', 'stats'));
    }

    // Update quote status (e.g. draft → sent → approved)
    public function updateStatus(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,sent,approved'],
        ]);

        $quote->update($data);

        return back()->with('success', 'تم تحديث حالة العرض.');
    }

    // Step 1 of conversion: a form listing every quote item so the user can
    // mark which ones were already purchased (and fill qty/cost/sell), which
    // then get recorded as real material purchases when the project is created.
    public function convertForm(Quote $quote)
    {
        abort_unless($quote->status === 'approved', 400, 'لازم اعتماد العرض الأول.');
        abort_if($quote->project_id, 400, 'تم تحويل هذا العرض لمشروع بالفعل.');

        $quote->load('bands.items');
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('quotes.convert', compact('quote', 'suppliers'));
    }

    // Step 2: turn an approved quote into a real project — locks the quote's
    // total as the initial contract value, seeds one band per quote line, and
    // records the items marked "already purchased" as real material purchases
    // (which debit محفظة المقاولات via the observer). All in one transaction so
    // an over-budget purchase rolls the whole conversion back.
    public function convertToProject(Request $request, Quote $quote)
    {
        abort_unless($quote->status === 'approved', 400, 'لازم اعتماد العرض الأول.');
        abort_if($quote->project_id, 400, 'تم تحويل هذا العرض لمشروع بالفعل.');

        $data = $request->validate([
            'items'                    => ['nullable', 'array'],
            'items.*.purchased'        => ['nullable', 'boolean'],
            'items.*.name'             => ['required', 'string', 'max:255'],
            'items.*.quote_band_id'    => ['required', 'integer'],
            'items.*.qty'              => ['nullable', 'numeric', 'min:0'],
            'items.*.unit'             => ['nullable', 'string', 'max:50'],
            'items.*.unit_price'       => ['nullable', 'numeric', 'min:0'],
            'items.*.sell_price'       => ['nullable', 'numeric', 'min:0'],
            'items.*.supervision_pct'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.supplier_id'      => ['nullable', 'exists:sy2_suppliers,id'],
            'items.*.date'             => ['nullable', 'date'],
            'items.*.payment_status'   => ['nullable', 'in:paid,partial,deferred'],
            'items.*.paid_amount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $quote->load('bands');

        $project = DB::transaction(function () use ($quote, $data) {
            $client = $quote->client_id
                ? Client::findOrFail($quote->client_id)
                : Client::firstOrCreate(
                    ['name' => $quote->client_name, 'phone' => $quote->phone],
                    ['address' => $quote->address]
                );

            $project = Project::create([
                'client_id'              => $client->id,
                'name'                   => $quote->client_name,
                'address'                => $quote->address,
                'area'                   => $quote->area,
                'initial_contract_value' => $quote->total(),
            ]);

            // Create bands and remember which project band each quote band maps to
            $bandMap = [];
            foreach ($quote->bands as $i => $band) {
                $bandMap[$band->id] = $project->bands()->create([
                    'name'         => $band->name,
                    'client_price' => $band->price,
                    'status'       => 'pending',
                    'sort_order'   => $i,
                ])->id;
            }

            // Record the items marked "already purchased" as real purchases
            foreach ($data['items'] ?? [] as $item) {
                if (empty($item['purchased'])) {
                    continue;
                }

                $paymentStatus = $item['payment_status'] ?? 'paid';
                $unitPrice     = (float) ($item['unit_price'] ?? 0);
                $qty           = (float) ($item['qty'] ?? 1);
                $totalCost     = $unitPrice * $qty;

                // For partial payment: use the entered paid_amount
                // For deferred: paid_amount = 0
                $paidAmount = match ($paymentStatus) {
                    'partial'  => (float) ($item['paid_amount'] ?? 0),
                    'deferred' => 0,
                    default    => $totalCost, // paid = full cost
                };

                Material::create([
                    'project_id'      => $project->id,
                    'band_id'         => $bandMap[$item['quote_band_id']] ?? null,
                    'supplier_id'     => $item['supplier_id'] ?? null,
                    'category'        => 'material',
                    'item'            => $item['name'],
                    'unit'            => $item['unit'] ?: 'وحدة',
                    'qty'             => $qty,
                    'unit_price'      => $unitPrice,
                    'sell_price'      => $item['sell_price'] ?? $unitPrice,
                    'supervision_pct' => $item['supervision_pct'] ?? 0,
                    'date'            => $item['date'] ?? today(),
                    'payment_status'  => $paymentStatus,
                    'paid_amount'     => $paidAmount,
                ]);
            }

            $quote->update(['project_id' => $project->id]);

            return $project;
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم إنشاء المشروع بقيمة تعاقد مبدئي ' . number_format($quote->total()) . ' ج.م.');
    }

    // Delete a quote
    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')
            ->with('success', 'تم حذف العرض.');
    }

    // Shared validation for store() and update() — $refRule differs because
    // update() must exclude the quote's own row from the uniqueness check
    private function validateQuote(Request $request, array $refRule): array
    {
        return $request->validate([
            ...$refRule,
            'client_id'   => ['required', 'exists:sy2_clients,id'],
            'address'     => ['nullable', 'string'],
            'area'        => ['nullable', 'numeric', 'min:0'],
            'date'        => ['required', 'date'],
            'status'      => ['required', 'in:draft,sent,approved'],
            'note'        => ['nullable', 'string'],
            // band arrays: bands[0][name], bands[0][price], optionally bands[0][items][0][name/qty/unit_price]
            'bands'                        => ['nullable', 'array'],
            'bands.*.name'                 => ['required', 'string', 'max:255'],
            'bands.*.price'                => ['nullable', 'numeric', 'min:0'],
            'bands.*.items'                => ['nullable', 'array'],
            'bands.*.items.*.name'         => ['required', 'string', 'max:255'],
            'bands.*.items.*.qty'          => ['required', 'numeric', 'min:0'],
            'bands.*.items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'bands.*.items.*.supervision_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    // Creates the bands (and their itemized breakdown, if any) for a quote.
    // A band with items gets its price recomputed server-side from those items
    // — the client-side sum is just a convenience preview, not the source of truth.
    private function saveBands(Quote $quote, array $bands): void
    {
        foreach ($bands as $i => $bandData) {
            $band = $quote->bands()->create([
                'name'       => $bandData['name'],
                'price'      => $bandData['price'] ?? 0,
                'sort_order' => $i,
            ]);

            foreach ($bandData['items'] ?? [] as $j => $item) {
                $band->items()->create([
                    'name'            => $item['name'],
                    'qty'             => $item['qty'],
                    'unit_price'      => $item['unit_price'],
                    'supervision_pct' => $item['supervision_pct'] ?? 0,
                    'sort_order'      => $j,
                ]);
            }

            if (! empty($bandData['items'])) {
                $band->load('items');
                $band->update(['price' => $band->itemsTotal()]);
            }
        }
    }
}
