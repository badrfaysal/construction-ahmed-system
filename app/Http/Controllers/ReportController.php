<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\Transaction;
use App\Support\ItemNameMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Entry screen for client statements — pick a project to view/print its statement
    public function statementIndex()
    {
        $projects = Project::with(['client'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($project) {
                $project->contract_value = $project->initialContractValue();
                $project->total_paid     = $project->totalCollected();
                $project->balance        = $project->actualClientTotal() - $project->total_paid;
                return $project;
            });

        return view('reports.statement-index', compact('projects'));
    }

    // Profitability table — real sell prices vs cost, split into book profit and collected profit
    public function profitability()
    {
        $projects = Project::with(['client'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($project) {
                $billed    = $project->actualClientTotal(); // sell price + supervision on every item
                $spent     = $project->totalSpent();        // real purchase cost + labor
                $collected = $project->totalCollected();    // cash actually received

                $project->contract_value   = $project->initialContractValue();
                $project->total_billed     = $billed;
                $project->total_spent      = $spent;
                $project->total_collected  = $collected;
                // ربح دفتري = ما سنحصل عليه (على الورق) - ما صرفناه
                $project->book_profit      = $billed - $spent;
                // ربح محصل = ما قبضناه فعلاً - ما صرفناه
                $project->earned_profit    = $collected - $spent;
                $project->book_margin      = $billed > 0 ? ($project->book_profit / $billed) * 100 : 0;

                return $project;
            });

        $totals = [
            'contract_value'  => $projects->sum('contract_value'),
            'total_billed'    => $projects->sum('total_billed'),
            'total_spent'     => $projects->sum('total_spent'),
            'total_collected' => $projects->sum('total_collected'),
            'book_profit'     => $projects->sum('book_profit'),
            'earned_profit'   => $projects->sum('earned_profit'),
        ];

        return view('reports.profitability', compact('projects', 'totals'));
    }

    // "التقارير" — detailed profit/expense breakdown across the whole business:
    // top projects, top bands (both by name across projects and per instance),
    // and top technicians, with optional date/project filters. Feeds the
    // Chart.js charts in reports/dashboard.blade.php.
    public function dashboard(Request $request)
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->filled('to') ? \Carbon\Carbon::parse($request->to)->endOfDay() : null;
        $projectId = $request->get('project_id');

        $projectsQuery = Project::with(['client', 'bands.workers']);
        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }
        $projects = $projectsQuery->get();

        // ---- Summary KPIs ----
        $totalProfit    = $projects->sum(fn ($p) => $p->bands->sum(fn ($b) => $b->profit()));
        $totalSpent     = $projects->sum(fn ($p) => $p->totalSpent());
        $totalCollected = $projects->sum(fn ($p) => $p->totalCollected());

        // ---- Monthly cash flow chart (same grouping as AnalyticsController::index()) ----
        $txQuery = Transaction::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            'direction',
            DB::raw('SUM(amount) as total')
        );
        if ($projectId) {
            $txQuery->where('project_id', $projectId);
        }
        if ($from) {
            $txQuery->where('date', '>=', $from);
        }
        if ($to) {
            $txQuery->where('date', '<=', $to);
        }
        if (! $from && ! $to) {
            $txQuery->where('date', '>=', today()->subMonths(5)->startOfMonth());
        }
        $cashFlow = [];
        foreach ($txQuery->groupBy('month', 'direction')->orderBy('month')->get() as $row) {
            $cashFlow[$row->month][$row->direction] = (float) $row->total;
        }

        // ---- Top projects by spend / by profit ----
        $projectRanking = $projects->map(fn ($p) => (object) [
            'name'   => $p->name,
            'spent'  => $p->totalSpent(),
            'profit' => $p->bands->sum(fn ($b) => $b->profit()),
        ]);
        $topProjectsBySpend  = $projectRanking->sortByDesc('spent')->take(5)->values();
        $topProjectsByProfit = $projectRanking->sortByDesc('profit')->take(5)->values();

        // ---- Bands: per-instance ranking + grouped-by-name across all projects ----
        $bandInstances = collect();
        $allBands = collect();
        foreach ($projects as $p) {
            foreach ($p->bands as $b) {
                $bandInstances->push((object) [
                    'name' => $b->name, 'project' => $p->name,
                    'spent' => $b->totalCost(), 'profit' => $b->profit(),
                ]);
                $allBands->push($b);
            }
        }
        $topBandInstancesBySpend  = $bandInstances->sortByDesc('spent')->take(5)->values();
        $topBandInstancesByProfit = $bandInstances->sortByDesc('profit')->take(5)->values();

        $bandGroups = collect(ItemNameMatcher::group($allBands, fn ($b) => $b->name))
            ->map(fn ($g) => (object) [
                'name'   => $g['canonical'],
                'count'  => count($g['items']),
                'spent'  => collect($g['items'])->sum(fn ($b) => $b->totalCost()),
                'profit' => collect($g['items'])->sum(fn ($b) => $b->profit()),
            ]);
        $topBandNamesBySpend  = $bandGroups->sortByDesc('spent')->take(5)->values();
        $topBandNamesByProfit = $bandGroups->sortByDesc('profit')->take(5)->values();

        // ---- Top technicians — merges the simple team_name field with
        // itemized BandWorker rows, grouped by normalized name only (no fuzzy
        // matching here, to avoid accidentally merging two different people) ----
        $people = collect();
        foreach ($allBands as $b) {
            if ($b->workers->isEmpty() && $b->team_name) {
                $people->push((object) ['name' => $b->team_name, 'amount' => (float) $b->labor_amount]);
            }
            foreach ($b->workers as $w) {
                $people->push((object) ['name' => $w->name, 'amount' => (float) $w->amount]);
            }
        }
        $technicians = $people
            ->groupBy(fn ($p) => ItemNameMatcher::normalize($p->name))
            ->map(fn ($group) => (object) [
                'name'  => $group->first()->name,
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $allProjects = Project::orderBy('name')->get(['id', 'name']);

        return view('reports.dashboard', compact(
            'from', 'to', 'projectId', 'allProjects',
            'totalProfit', 'totalSpent', 'totalCollected', 'cashFlow',
            'topProjectsBySpend', 'topProjectsByProfit',
            'topBandInstancesBySpend', 'topBandInstancesByProfit',
            'topBandNamesBySpend', 'topBandNamesByProfit',
            'technicians'
        ));
    }

    // Printable client statement (كشف حساب) for one project —
    // shows every material/labor cost grouped by band, plus the payment plan
    public function clientStatement(Project $project)
    {
        $project->load([
            'client',
            'bands.materials.returns',
            'materials.returns',
            'installments' => fn ($q) => $q->orderBy('due_date'),
        ]);

        // Only bands that have actually started carry real spend worth listing
        $spentBands = $project->bands->where('status', '!=', 'pending');

        // Petty/misc expenses registered without a specific band (band_id null)
        $generalMaterials = $project->generalMaterials()->sortBy('date');

        $initialContractValue = $project->initialContractValue();
        $totalPaid            = $project->totalCollected();
        $actualTotal          = $spentBands->sum(fn ($band) => $band->actualClientTotal())
            + $generalMaterials->sum(fn ($m) => $m->netClientCost());
        $balance              = $actualTotal - $totalPaid;

        return view('reports.statement', compact(
            'project', 'spentBands', 'generalMaterials', 'initialContractValue', 'totalPaid', 'actualTotal', 'balance'
        ));
    }

    // Printable internal cost statement for a whole project — every band, every
    // purchase (item, qty, real cost, supplier), returns, wages, and the total
    // cost per band and for the project. Admin-only: shows real cost & profit,
    // the counterpart to the client statement.
    public function companyStatement(Project $project)
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);

        $project->load([
            'client',
            'bands.materials.supplier',
            'bands.materials.returns',
            'bands.workers',
            'installments',
        ]);

        $totalCost      = $project->bands->sum(fn ($b) => $b->totalCost());
        $totalBilled    = $project->actualClientTotal();
        $totalCollected = $project->totalCollected();
        $totalProfit    = $project->bands->sum(fn ($b) => $b->profit());

        return view('reports.company-statement', compact(
            'project', 'totalCost', 'totalBilled', 'totalCollected', 'totalProfit'
        ));
    }

    // Printable internal statement for a single band — exactly where the money
    // went (every material + every worker/wage) and the resulting profit.
    // Owner-only: unlike the client statement, this shows real cost & margin.
    public function bandStatement(ProjectBand $band)
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);

        $band->load(['project.client', 'materials.supplier', 'materials.returns', 'workers']);

        return view('reports.band-statement', compact('band'));
    }

    // تقدير تكلفة مشروع جديد بالاعتماد على مشروع سابق كمرجع: اختار مشروع
    // (مثلاً شقة 100م) وشوف بالظبط كل بند اشتغلت فيه، وكل خامة اشتريتها له
    // بكميتها وتكلفتها، عشان لو جالك مشروع تاني بنفس المساحة يبقى عندك تقدير
    // جاهز لكل حاجة هتحتاجها (خامات + مصنعية) بالتفصيل.
    public function estimationIndex()
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);

        $projects = Project::withCount('bands')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'area', 'status', 'client_id'])
            ->load('client');

        return view('reports.estimation-index', compact('projects'));
    }

    public function estimationShow(Project $project)
    {
        abort_unless(auth()->user()->canSeeFinancials(), 403);

        $project->load(['client', 'bands.materials.returns', 'bands.workers.payments']);

        $area = (float) $project->area;

        $bands = $project->bands->map(function ($band) use ($area) {
            $materials = $band->materials
                ->groupBy('item')
                ->map(function ($group) {
                    return (object) [
                        'item' => $group->first()->item,
                        'unit' => $group->first()->unit,
                        'qty'  => $group->sum(fn ($m) => $m->netQty()),
                        'cost' => $group->sum(fn ($m) => $m->netCost()),
                    ];
                })
                ->sortByDesc('cost')
                ->values();

            $workers = $band->workers->map(fn ($w) => (object) [
                'name'          => $w->name,
                'contract_type' => $w->contractTypeAr(),
                'qty'           => in_array($w->contract_type, ['per_meter','per_piece','daily']) ? $w->contract_qty : null,
                'unit_rate'     => in_array($w->contract_type, ['per_meter','per_piece','daily']) ? (float) $w->contract_unit_rate : null,
                'amount'        => (float) $w->amount,
                'paid'          => (float) $w->paidTotal(),
                'remaining'     => (float) $w->remaining(),
            ])->sortByDesc('amount')->values();

            $materialCost = $materials->sum('cost');
            $laborCost    = (float) $band->labor_amount;
            $totalCost    = $materialCost + $laborCost;

            return (object) [
                'band'          => $band,
                'materials'     => $materials,
                'workers'       => $workers,
                'material_cost' => $materialCost,
                'labor_cost'    => $laborCost,
                'total_cost'    => $totalCost,
                'per_sqm'       => $area > 0 ? $totalCost / $area : null,
            ];
        });

        $totalMaterialCost = $bands->sum('material_cost');
        $totalLaborCost    = $bands->sum('labor_cost');
        $grandTotal        = $totalMaterialCost + $totalLaborCost;
        $grandPerSqm       = $area > 0 ? $grandTotal / $area : null;

        return view('reports.estimation-show', compact(
            'project', 'bands', 'area',
            'totalMaterialCost', 'totalLaborCost', 'grandTotal', 'grandPerSqm'
        ));
    }
}
