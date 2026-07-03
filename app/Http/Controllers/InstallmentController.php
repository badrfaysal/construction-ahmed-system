<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    // List installments grouped by project (card-per-project style)
    public function index(Request $request)
    {
        // Load every project that has at least one installment, with full context
        $projectsQuery = Project::with([
            'client',
            'installments' => fn ($q) => $q->orderBy('sort_order')->orderBy('due_date'),
            'installments.band',
            'bands.materials.returns',
            'bands.workers',
        ])->has('installments');

        if ($pid = $request->get('project_id')) {
            $projectsQuery->where('id', $pid);
        }

        $statusFilter = $request->get('status'); // 'paid','due','upcoming', or null

        $projects = $projectsQuery->orderByDesc('created_at')->get();

        // Enrich each project with installment KPIs used by the view
        $projects = $projects->map(function ($project) use ($statusFilter) {
            $insts  = $statusFilter
                ? $project->installments->where('status', $statusFilter)
                : $project->installments;

            $paid   = $project->installments->where('status', 'paid');
            $due    = $project->installments->where('status', 'due');
            $upcoming = $project->installments->where('status', 'upcoming');

            $project->inst_total    = $project->installments->sum('amount');
            $project->inst_paid     = $paid->sum('amount');
            $project->inst_remaining = $project->installments->whereIn('status', ['due','upcoming'])->sum('amount');
            $project->inst_due_cnt  = $due->count();
            $project->inst_paid_cnt = $paid->count();
            $project->inst_total_cnt = $project->installments->count();
            $project->inst_progress = $project->inst_total > 0
                ? round($project->inst_paid / $project->inst_total * 100)
                : 0;
            $project->billed_total  = $project->actualClientTotal();
            // How much of what we billed is already collected
            $project->collect_pct   = $project->billed_total > 0
                ? round($project->inst_paid / $project->billed_total * 100)
                : 0;
            $project->filtered_insts = $insts->values();

            return $project;
        });

        // Summary KPIs across all projects
        $totals = [
            'projects'  => $projects->count(),
            'total'     => $projects->sum('inst_total'),
            'paid'      => $projects->sum('inst_paid'),
            'remaining' => $projects->sum('inst_remaining'),
            'overdue'   => $projects->sum('inst_due_cnt'),
        ];

        $allProjects = Project::orderBy('name')->get(['id', 'name']);

        return view('installments.index', compact('projects', 'totals', 'allProjects', 'statusFilter'));
    }

    // Show form to add a single installment to a project
    public function create(Request $request)
    {
        $projects          = Project::orderBy('name')->get(['id', 'name']);
        $selectedProjectId = $request->get('project_id');
        $bands             = $selectedProjectId
            ? Project::find($selectedProjectId)?->bands()->get(['id', 'name']) ?? collect()
            : collect();

        return view('installments.create', compact('projects', 'selectedProjectId', 'bands'));
    }

    // Save a new installment
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'     => ['required', 'exists:sy2_projects,id'],
            'band_id'        => ['nullable', 'exists:sy2_project_bands,id'],
            'label'          => ['required', 'string', 'max:255'],
            'due_date'       => ['required', 'date'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'status'         => ['required', 'in:paid,due,upcoming'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'paid_date'      => ['nullable', 'date'],
        ]);

        DB::transaction(fn () => Installment::create($data));

        return redirect()->route('projects.show', $data['project_id'])
            ->with('success', 'تم إضافة القسط.');
    }

    // Show the installment plan generator form
    public function planForm(Request $request)
    {
        $projects          = Project::orderBy('name')->get(['id', 'name']);
        $selectedProjectId = $request->get('project_id');
        $bands             = $selectedProjectId
            ? Project::find($selectedProjectId)?->bands()->get(['id', 'name']) ?? collect()
            : collect();
        $project = $selectedProjectId ? Project::find($selectedProjectId) : null;

        return view('installments.plan', compact('projects', 'selectedProjectId', 'bands', 'project'));
    }

    // Generate a series of installments from a plan (down payment + monthly installments)
    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'project_id'     => ['required', 'exists:sy2_projects,id'],
            'band_id'        => ['nullable', 'exists:sy2_project_bands,id'],
            'total_amount'   => ['required', 'numeric', 'min:1'],
            'down_payment'   => ['required', 'numeric', 'min:0'],
            'months'         => ['required', 'integer', 'min:1', 'max:120'],
            'interest_rate'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date'     => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $total        = (float) $data['total_amount'];
        $down         = (float) $data['down_payment'];
        $months       = (int)   $data['months'];
        $interestRate = (float) ($data['interest_rate'] ?? 0);
        $remaining    = $total - $down;

        // Apply flat interest on remaining balance
        $totalWithInterest = $remaining * (1 + $interestRate / 100);
        $monthly = $months > 0 ? $totalWithInterest / $months : 0;

        DB::transaction(function () use ($data, $down, $monthly, $months, $totalWithInterest) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $sort = 0;

            // Down payment row
            if ($down > 0) {
                Installment::create([
                    'project_id'     => $data['project_id'],
                    'band_id'        => $data['band_id'] ?? null,
                    'label'          => 'دفعة مقدم',
                    'amount'         => $down,
                    'due_date'       => $startDate->toDateString(),
                    'status'         => 'upcoming',
                    'payment_method' => $data['payment_method'] ?? null,
                    'sort_order'     => $sort++,
                ]);
            }

            // Monthly installments
            for ($i = 1; $i <= $months; $i++) {
                Installment::create([
                    'project_id'     => $data['project_id'],
                    'band_id'        => $data['band_id'] ?? null,
                    'label'          => 'القسط ' . $i . ' من ' . $months,
                    'amount'         => round($monthly, 2),
                    'due_date'       => $startDate->copy()->addMonths($i)->toDateString(),
                    'status'         => 'upcoming',
                    'payment_method' => $data['payment_method'] ?? null,
                    'sort_order'     => $sort++,
                ]);
            }
        });

        return redirect()->route('installments.index', ['project_id' => $data['project_id']])
            ->with('success', 'تم إنشاء خطة التقسيط بنجاح.');
    }

    // Printable / WhatsApp-shareable installment statement for one project
    public function statement(Project $project)
    {
        $project->load([
            'client',
            'installments' => fn ($q) => $q->orderBy('sort_order')->orderBy('due_date'),
            'installments.band',
        ]);

        $installments = $project->installments;
        $downPaymentRow = $installments->firstWhere('label', 'دفعة مقدم');
        $downPaid       = $installments->where('status', 'paid')->sum('amount');
        $monthlyInsts   = $installments->where('label', '!=', 'دفعة مقدم');
        $monthCount     = $monthlyInsts->count();
        $totalWithInst  = $installments->sum('amount');
        $remaining      = $totalWithInst - $downPaid;
        $avgMonthly     = $monthCount > 0 ? $monthlyInsts->avg('amount') : 0;

        // Preferred payment day (day-of-month of first monthly installment)
        $firstMonthly = $monthlyInsts->first();
        $payDay       = $firstMonthly ? $firstMonthly->due_date->day : null;

        $settings = \App\Models\Settings::current();

        return view('installments.statement', compact(
            'project', 'installments', 'downPaid', 'monthCount',
            'totalWithInst', 'remaining', 'avgMonthly', 'payDay', 'settings'
        ));
    }

    // JSON endpoint: returns billed amount + paid installments for plan auto-fill
    // GET /api/projects/{project}/plan-data?band_id=X
    public function planData(Project $project)
    {
        $bandId = request('band_id');

        $project->load(['bands.materials.returns', 'bands.workers', 'installments']);

        if ($bandId) {
            $band   = $project->bands->firstWhere('id', $bandId);
            $billed = $band ? $band->actualClientTotal() : 0;
            $paid   = $project->installments
                ->where('status', 'paid')
                ->where('band_id', $bandId)
                ->sum('amount');
            $name = $band?->name ?? '';
        } else {
            $billed = $project->actualClientTotal();
            $paid   = $project->installments->where('status', 'paid')->sum('amount');
            $name   = $project->name;
        }

        return response()->json([
            'billed' => round($billed, 2),
            'paid'   => round($paid, 2),
            'name'   => $name,
        ]);
    }

    // Mark an installment as paid (quick action from the list)
    public function markPaid(Installment $installment)
    {
        DB::transaction(fn () => $installment->update([
            'status'    => 'paid',
            'paid_date' => today(),
        ]));

        return back()->with('success', 'تم تسجيل الدفع.');
    }

    // Delete
    public function destroy(Installment $installment)
    {
        DB::transaction(fn () => $installment->delete());
        return back()->with('success', 'تم حذف القسط.');
    }
}
