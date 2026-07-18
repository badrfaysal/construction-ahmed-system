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

        $query = Quote::with('bands.items', 'bands.workers');

        if ($q = $request->get('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('project_name', 'like', '%' . \App\Support\ItemNameMatcher::normalizeLetters($q) . '%')
                   ->orWhere('client_name', 'like', '%' . \App\Support\ItemNameMatcher::normalizeLetters($q) . '%');
            });
        }

        match ($request->get('sort', 'newest')) {
            'oldest'       => $query->orderBy('date')->orderBy('id'),
            'project_asc'  => $query->orderBy('project_name'),
            'project_desc' => $query->orderByDesc('project_name'),
            default        => $query->orderByDesc('date')->orderByDesc('id'),
        };

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
        $quote->load('bands.items', 'bands.workers');
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

        $bandNames = \App\Models\ProjectBand::select('name')->distinct()->pluck('name')
            ->merge(\App\Models\QuoteBand::select('name')->distinct()->pluck('name'))
            ->unique()->sort()->values();

        return view('quotes.create', compact('nextRef', 'clients', 'bandNames'));
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

        $quote->load('bands.items', 'bands.workers');
        $clients = Client::orderBy('name')->get();

        $bandNames = \App\Models\ProjectBand::select('name')->distinct()->pluck('name')
            ->merge(\App\Models\QuoteBand::select('name')->distinct()->pluck('name'))
            ->unique()->sort()->values();

        return view('quotes.edit', compact('quote', 'clients', 'bandNames'));
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
        $quotes = Quote::with('bands.items', 'bands.workers')
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

        $quote->load('bands.items', 'bands.workers');
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $wallets   = \App\Models\Account::selectable();

        return view('quotes.convert', compact('quote', 'suppliers', 'wallets'));
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
            'default_supervision_pct'  => ['required', 'numeric', 'min:0', 'max:100'],
            'account_id'               => ['nullable', 'integer', 'exists:accounts,id'],
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

        $quote->load('bands.workers');

        $project = DB::transaction(function () use ($quote, $data) {
            $client = $quote->client_id
                ? Client::findOrFail($quote->client_id)
                : Client::firstOrCreate(
                    ['name' => $quote->client_name, 'phone' => $quote->phone],
                    ['address' => $quote->address]
                );

            $project = Project::create([
                'client_id'               => $client->id,
                'name'                    => $quote->client_name,
                'address'                 => $quote->address,
                'area'                    => $quote->area,
                'initial_contract_value'  => $quote->total(),
                'default_supervision_pct' => $data['default_supervision_pct'],
            ]);



            // Create bands and remember which project band each quote band maps to
            $bandMap = [];
            foreach ($quote->bands as $i => $band) {
                $projectBand = $project->bands()->create([
                    'name'         => $band->name,
                    'client_price' => $band->price,
                    'status'       => 'pending',
                    'sort_order'   => $i,
                ]);
                $bandMap[$band->id] = $projectBand->id;

                // انسخ فنيي البند من العرض (لو موجودين) كما هم — نفس المصنعية
                // المتفق عليها في العرض تبقى نقطة البداية الحقيقية للمشروع،
                // بدل ما تتكتب يدوي تاني من الصفر
                foreach ($band->workers as $j => $worker) {
                    $projectBand->workers()->create([
                        'name'               => $worker->name,
                        'specialty'          => $worker->specialty,
                        'contract_type'      => $worker->contract_type,
                        'contract_qty'       => $worker->contract_qty,
                        'contract_unit_rate' => $worker->contract_unit_rate,
                        'sell_rate'          => $worker->sell_rate,
                        'amount'             => $worker->amount,
                        'sell_amount'        => $worker->sell_amount,
                        'supervision_pct'    => $worker->supervision_pct,
                        'notes'              => $worker->notes,
                        'sort_order'         => $j,
                    ]);
                }
                if ($band->workers->isNotEmpty()) {
                    $projectBand->recomputeLaborTotals();
                }
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
                    'account_id'      => $data['account_id'] ?? null,
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
            // المصنعية والفنيين — تفصيلة اختيارية لكل بند، بنفس منطق sy2_band_workers
            'bands.*.workers'                        => ['nullable', 'array'],
            'bands.*.workers.*.name'                 => ['required', 'string', 'max:255'],
            'bands.*.workers.*.specialty'             => ['nullable', 'string', 'max:255'],
            'bands.*.workers.*.contract_type'         => ['nullable', 'in:lump_sum,daily,per_meter,per_piece'],
            'bands.*.workers.*.contract_qty'          => ['nullable', 'numeric', 'min:0'],
            'bands.*.workers.*.contract_unit_rate'    => ['nullable', 'numeric', 'min:0'],
            'bands.*.workers.*.sell_rate'             => ['nullable', 'numeric', 'min:0'],
            'bands.*.workers.*.amount'                => ['nullable', 'numeric', 'min:0'],
            'bands.*.workers.*.sell_amount'           => ['nullable', 'numeric', 'min:0'],
            'bands.*.workers.*.supervision_pct'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bands.*.workers.*.notes'                 => ['nullable', 'string'],
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

            foreach ($bandData['workers'] ?? [] as $k => $worker) {
                $band->workers()->create([
                    'name'               => $worker['name'],
                    'specialty'          => $worker['specialty'] ?? null,
                    'contract_type'      => $worker['contract_type'] ?? null,
                    'contract_qty'       => $worker['contract_qty'] ?? null,
                    'contract_unit_rate' => $worker['contract_unit_rate'] ?? null,
                    'sell_rate'          => $worker['sell_rate'] ?? null,
                    'amount'             => $worker['amount'] ?? 0,
                    'sell_amount'        => $worker['sell_amount'] ?? 0,
                    'supervision_pct'    => $worker['supervision_pct'] ?? 0,
                    'notes'              => $worker['notes'] ?? null,
                    'sort_order'         => $k,
                ]);
            }

            if (! empty($bandData['items']) || ! empty($bandData['workers'])) {
                $band->load('items', 'workers');
                $band->update(['price' => $band->itemsTotal()]);
            }
        }
    }
}
