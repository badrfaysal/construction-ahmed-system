<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\Material;
use App\Models\MaterialReturn;
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
                $billed    = $project->grossClientTotal(); // الإجمالي قبل الخصم
                $spent     = $project->totalSpent();        // real purchase cost + labor
                $collected = $project->totalCollected();    // cash actually received

                $project->contract_value   = $project->initialContractValue();
                $project->total_billed     = $billed;
                $project->total_spent      = $spent;
                $project->total_collected  = $collected;
                $project->total_discount   = $project->totalDiscount();
                // ربح دفتري = ما سنحصل عليه (على الورق) - ما صرفناه - الخصومات
                $project->book_profit      = $billed - $spent - $project->total_discount;
                // ربح محصل = ما قبضناه فعلاً - ما صرفناه
                $project->earned_profit    = $collected - $spent;
                $project->book_margin      = $billed > 0 ? ($project->book_profit / $billed) * 100 : 0;

                // تفصيل الربح الدفتري لمصدرين منفصلين:
                //  - تجاري: فرق سعر الشراء عن سعر البيع بس (هامش تجاري بحت)
                //  - نسبة: نسبة الإشراف المضافة فوق سعر البيع
                // مجموعهم كان يساوي book_profit قبل خصم الدفعات
                $trade = $project->tradeProfit();
                $pct   = $project->percentageProfit();
                $project->trade_profit      = $trade;
                $project->percentage_profit = $pct;
                $profitBase = $trade + $pct; // = book_profit، بنستخدمه كمقام للنسب عشان نتجنب قسمة مختلفة لو فيه فروق تقريب
                $project->trade_profit_share = abs($profitBase) > 0.009 ? ($trade / $profitBase) * 100 : 0;
                $project->percentage_profit_share = abs($profitBase) > 0.009 ? ($pct / $profitBase) * 100 : 0;

                return $project;
            });

        $totals = [
            'contract_value'  => $projects->sum('contract_value'),
            'total_billed'    => $projects->sum('total_billed'),
            'total_spent'     => $projects->sum('total_spent'),
            'total_collected' => $projects->sum('total_collected'),
            'total_discount'  => $projects->sum('total_discount'),
            'book_profit'     => $projects->sum('book_profit'),
            'earned_profit'   => $projects->sum('earned_profit'),
            'trade_profit'      => $projects->sum('trade_profit'),
            'percentage_profit' => $projects->sum('percentage_profit'),
        ];
        $totalProfitBase = $totals['trade_profit'] + $totals['percentage_profit'];
        $totals['trade_profit_share'] = abs($totalProfitBase) > 0.009 ? ($totals['trade_profit'] / $totalProfitBase) * 100 : 0;
        $totals['percentage_profit_share'] = abs($totalProfitBase) > 0.009 ? ($totals['percentage_profit'] / $totalProfitBase) * 100 : 0;

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
        // $p->profit() (مش bands->sum) عشان يشمل نثريات/خامات عامة (band_id
        // null) — الجمع على البنود لوحدها كان بيسيبها برا الحساب تمامًا
        $totalProfit    = $projects->sum(fn ($p) => $p->profit());
        $totalSpent     = $projects->sum(fn ($p) => $p->totalSpent());
        $totalCollected = $projects->sum(fn ($p) => $p->totalCollected());
        $totalDiscounts = $projects->sum(fn ($p) => $p->totalDiscount());

        $topDiscountProject = $projects->sortByDesc(fn ($p) => $p->totalDiscount())->first();
        if ($topDiscountProject && $topDiscountProject->totalDiscount() == 0) {
            $topDiscountProject = null;
        }

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

        // ---- Top projects by spend / by profit / by discount ----
        $projectRanking = $projects->map(fn ($p) => (object) [
            'name'     => $p->name,
            'spent'    => $p->totalSpent(),
            'profit'   => $p->profit(),
            'discount' => $p->totalDiscount(),
        ]);
        $topProjectsBySpend    = $projectRanking->sortByDesc('spent')->take(5)->values();
        $topProjectsByProfit   = $projectRanking->sortByDesc('profit')->take(5)->values();
        
        $discountedProjects = $projectRanking->filter(fn ($p) => $p->discount > 0);
        $topProjectsByDiscount = $discountedProjects->sortByDesc('discount')->take(5)->values();
        $lowestProjectsByDiscount = $discountedProjects->sortBy('discount')->take(5)->values();

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

        // ---- Top purchased materials (grouped by item name) ----
        $matQuery = Material::where('category', '!=', 'misc')
            ->with('returns');
        if ($projectId) {
            $matQuery->where('project_id', $projectId);
        }
        if ($from) {
            $matQuery->whereDate('date', '>=', $from);
        }
        if ($to) {
            $matQuery->whereDate('date', '<=', $to);
        }
        $allMaterials = $matQuery->get();

        $topPurchasedMaterials = $allMaterials
            ->groupBy(fn ($m) => mb_strtolower(trim($m->item)))
            ->map(fn ($group) => (object) [
                'item'     => $group->first()->item,
                'unit'     => $group->first()->unit,
                'total_qty'  => $group->sum('qty'),
                'net_qty'    => $group->sum(fn ($m) => $m->netQty()),
                'total_cost' => $group->sum(fn ($m) => $m->netCost()),
                'count'      => $group->count(),
            ])
            ->sortByDesc('total_cost')
            ->take(10)
            ->values();

        // ---- Top returned materials ----
        $topReturnedMaterials = $allMaterials
            ->filter(fn ($m) => $m->returns->isNotEmpty())
            ->groupBy(fn ($m) => mb_strtolower(trim($m->item)))
            ->map(fn ($group) => (object) [
                'item'          => $group->first()->item,
                'unit'          => $group->first()->unit,
                'returned_qty'  => $group->sum(fn ($m) => $m->returnedQty()),
                'returned_value'=> $group->sum(fn ($m) => $m->returnedQty() * (float) $m->unit_price),
                'return_count'  => $group->sum(fn ($m) => $m->returns->count()),
            ])
            ->filter(fn ($r) => $r->returned_qty > 0)
            ->sortByDesc('returned_value')
            ->take(10)
            ->values();

        // ---- Marketers ----
        $marketers = \App\Models\Marketer::all()->map(fn($m) => (object)[
            'name' => $m->name,
            'total_paid' => $m->totalPaid()
        ])->filter(fn($m) => $m->total_paid > 0);

        $totalMarketerCommissions = $marketers->sum('total_paid');
        $topMarketers = $marketers->sortByDesc('total_paid')->take(5)->values();
        $lowestMarketers = $marketers->sortBy('total_paid')->take(5)->values();

        return view('reports.dashboard', compact(
            'from', 'to', 'projectId', 'allProjects',
            'totalProfit', 'totalSpent', 'totalCollected', 'totalDiscounts', 'topDiscountProject', 'cashFlow',
            'topProjectsBySpend', 'topProjectsByProfit', 'topProjectsByDiscount',
            'topBandInstancesBySpend', 'topBandInstancesByProfit',
            'topBandNamesBySpend', 'topBandNamesByProfit',
            'technicians',
            'topPurchasedMaterials', 'topReturnedMaterials',
            'lowestProjectsByDiscount',
            'totalMarketerCommissions', 'topMarketers', 'lowestMarketers'
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
        $subTotal             = $spentBands->sum(fn ($band) => $band->actualClientTotal())
            + $generalMaterials->sum(fn ($m) => $m->netClientCost());
            
        $discountAmount       = (float) $project->discount + (float) $project->discounts()->sum('amount');
        $actualTotal          = $subTotal - $discountAmount;
        $balance              = $actualTotal - $totalPaid;

        return view('reports.statement', compact(
            'project', 'spentBands', 'generalMaterials', 'initialContractValue', 'totalPaid', 'subTotal', 'discountAmount', 'actualTotal', 'balance'
        ));
    }

    // نسخة مختصرة من كشف حساب العميل: تكلفة كل بند كرقم واحد فقط بدون تفاصيل
    // الخامات والكميات — للعميل اللي عايز يشوف الإجمالي بسرعة بس
    public function clientStatementSummary(Project $project)
    {
        $project->load([
            'client',
            'bands.materials.returns',
            'materials.returns',
            'installments' => fn ($q) => $q->orderBy('due_date'),
        ]);

        $spentBands = $project->bands->where('status', '!=', 'pending');
        $generalMaterials = $project->generalMaterials()->sortBy('date');
        $generalTotal = $generalMaterials->sum(fn ($m) => $m->netClientCost());

        $initialContractValue = $project->initialContractValue();
        $totalPaid            = $project->totalCollected();
        $subTotal             = $spentBands->sum(fn ($band) => $band->actualClientTotal()) + $generalTotal;
        $discountAmount       = (float) $project->discount + (float) $project->discounts()->sum('amount');
        $actualTotal          = $subTotal - $discountAmount;
        $balance              = $actualTotal - $totalPaid;

        return view('reports.statement-summary', compact(
            'project', 'spentBands', 'generalTotal', 'initialContractValue', 'totalPaid', 'subTotal', 'discountAmount', 'actualTotal', 'balance'
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

        // $project->totalSpent()/profit() (مش bands->sum) عشان يشملوا
        // نثريات/خامات عامة على المشروع (band_id null) — لو جمعنا على البنود
        // بس، أي خامة مسجلة من غير بند بتختفي من التكلفة والربح خالص هنا،
        // مع إن $totalBilled أصلاً بيحسبها (actualClientTotal يشملها)
        $totalCost      = $project->totalSpent();
        $totalBilled    = $project->grossClientTotal();
        $totalCollected = $project->totalCollected();
        $totalProfit    = $project->profit();

        $generalMaterials = $project->generalMaterials();

        return view('reports.company-statement', compact(
            'project', 'totalCost', 'totalBilled', 'totalCollected', 'totalProfit', 'generalMaterials'
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

        // تسمية الوحدة حسب نوع تعاقد العمال — منها بنعرف "متر/قطعة/يوم"
        $unitLabels = ['per_meter' => 'متر', 'per_piece' => 'قطعة', 'daily' => 'يوم'];

        $bands = $project->bands->map(function ($band) use ($area, $unitLabels) {
            // نفصل الخامات الحقيقية عن النثريات (المصاريف المتنوعة category=misc)
            // عشان يبان كل نوع لوحده في التقدير
            $realMats = $band->materials->where('category', '!=', 'misc');
            $miscMats = $band->materials->where('category', 'misc');

            $materials = $realMats
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

            $petty = $miscMats->map(fn ($m) => (object) [
                'item' => $m->item,
                'date' => $m->date,
                'cost' => $m->netCost(),
            ])->sortByDesc('cost')->values();

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
            $pettyCost    = $petty->sum('cost');
            $laborCost    = (float) $band->labor_amount;
            $totalCost    = $materialCost + $pettyCost + $laborCost;

            // ── وحدة قياس البند وكميتها ─────────────────────────────────────
            // بنشتق "وحدة العمل" (متر حوائط / قطعة نجارة / نقطة كهرباء) من عمال
            // البند: بنجمع كمياتهم لكل نوع تعاقد، ولو فيه نوع واحد سايد نعتبره
            // وحدة البند. منها نطلع:
            //   • متوسط تكلفة الوحدة  = التكلفة الكلية للبند ÷ عدد الوحدات
            //   • الكثافة لكل 100م²   = عدد الوحدات ÷ مساحة الشقة × 100
            $qtyWorkers = $band->workers->filter(fn ($w) =>
                in_array($w->contract_type, ['per_meter', 'per_piece', 'daily'], true)
                && (float) $w->contract_qty > 0
            );
            $byType = $qtyWorkers->groupBy('contract_type');

            $unitType = null;
            $unitQty  = 0.0;
            if ($byType->count() === 1) {
                $unitType = $byType->keys()->first();
                $unitQty  = (float) $qtyWorkers->sum(fn ($w) => (float) $w->contract_qty);
            }
            $unitLabel   = $unitType ? $unitLabels[$unitType] : null;
            $costPerUnit = ($unitQty > 0) ? $totalCost / $unitQty : null;
            $density100  = ($unitQty > 0 && $area > 0) ? $unitQty / $area * 100 : null;

            return (object) [
                'band'           => $band,
                'materials'      => $materials,
                'petty'          => $petty,
                'workers'        => $workers,
                'material_cost'  => $materialCost,
                'petty_cost'     => $pettyCost,
                'labor_cost'     => $laborCost,
                'total_cost'     => $totalCost,
                'per_sqm'        => $area > 0 ? $totalCost / $area : null,
                'mat_per_sqm'    => $area > 0 ? $materialCost / $area : null,
                'petty_per_sqm'  => $area > 0 ? $pettyCost / $area : null,
                'labor_per_sqm'  => $area > 0 ? $laborCost / $area : null,
                // تحليل الوحدة (لو البند شغال بوحدة قياس واضحة)
                'unit_label'     => $unitLabel,
                'unit_qty'       => $unitQty,
                'cost_per_unit'  => $costPerUnit,
                'density_100'    => $density100,
            ];
        });

        // نثريات عامة على المشروع (مش مربوطة ببند محدّد)
        $generalPetty = $project->generalMaterials()
            ->where('category', 'misc')
            ->map(fn ($m) => (object) [
                'item' => $m->item,
                'date' => $m->date,
                'cost' => $m->netCost(),
            ])->sortByDesc('cost')->values();
        $generalPettyCost = $generalPetty->sum('cost');

        $totalMaterialCost = $bands->sum('material_cost');
        $totalPettyCost    = $bands->sum('petty_cost') + $generalPettyCost;
        $totalLaborCost    = $bands->sum('labor_cost');
        $grandTotal        = $totalMaterialCost + $totalPettyCost + $totalLaborCost;
        $grandPerSqm       = $area > 0 ? $grandTotal / $area : null;

        return view('reports.estimation-show', compact(
            'project', 'bands', 'area', 'generalPetty', 'generalPettyCost',
            'totalMaterialCost', 'totalPettyCost', 'totalLaborCost', 'grandTotal', 'grandPerSqm'
        ));
    }
}
