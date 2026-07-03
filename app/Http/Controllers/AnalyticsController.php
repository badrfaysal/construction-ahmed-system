<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Charts and KPIs: monthly cash flow, top suppliers, project status mix
    public function index()
    {
        // Cash in/out grouped by month, last 6 months
        $monthly = Transaction::select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                'direction',
                DB::raw('SUM(amount) as total')
            )
            ->where('date', '>=', today()->subMonths(5)->startOfMonth())
            ->groupBy('month', 'direction')
            ->orderBy('month')
            ->get();

        // Reshape into ['2026-01' => ['in' => x, 'out' => y], ...] for easy charting in the view
        $cashFlow = [];
        foreach ($monthly as $row) {
            $cashFlow[$row->month][$row->direction] = (float) $row->total;
        }

        // Top 5 suppliers by net spend (purchases minus returns)
        $topSuppliers = Supplier::with('materials.returns')
            ->get()
            ->map(function ($s) {
                $s->net_spend = $s->materials->sum(fn ($m) => $m->netCost());
                return $s;
            })
            ->sortByDesc('net_spend')
            ->take(5);

        // Project status breakdown
        $statusCounts = [
            'active' => Project::where('status', 'active')->count(),
            'done'   => Project::where('status', 'done')->count(),
        ];

        return view('analytics.index', compact('cashFlow', 'topSuppliers', 'statusCounts'));
    }

    // Labor cost breakdown grouped by technician / team name across all projects
    public function technicians()
    {
        $technicians = ProjectBand::select('team_name')
            ->selectRaw('COUNT(*) as bands_count')
            ->selectRaw('SUM(labor_amount) as total_labor')
            ->selectRaw('SUM(client_price) as total_client_price')
            ->whereNotNull('team_name')
            ->where('team_name', '!=', '')
            ->groupBy('team_name')
            ->orderByDesc('total_labor')
            ->get();

        return view('analytics.technicians', compact('technicians'));
    }
}
