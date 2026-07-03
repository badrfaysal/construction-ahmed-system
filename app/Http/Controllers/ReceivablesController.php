<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Project;
use Illuminate\Http\Request;

class ReceivablesController extends Controller
{
    // Shows what clients owe us — split into per-project receivables and overdue installments
    public function index(Request $request)
    {
        $projects = Project::with([
            'client',
            'bands.materials.returns',
            'bands.workers',
            'installments',
        ])->orderByDesc('created_at')->get();

        $rows = $projects->map(function ($project) {
            $billed    = $project->actualClientTotal();
            $collected = $project->totalCollected();
            $remaining = $billed - $collected;

            return (object) [
                'project'       => $project,
                'billed'        => $billed,
                'collected'     => $collected,
                'remaining'     => $remaining,
                'book_profit'   => $billed - $project->totalSpent(),
                'earned_profit' => $collected - $project->totalSpent(),
            ];
        })->filter(fn ($r) => $r->billed > 0);

        // Overdue installments across all projects
        $overdueInstallments = Installment::with(['project.client', 'band'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        // Upcoming installments (next 60 days)
        $upcomingInstallments = Installment::with(['project.client', 'band'])
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', today()->addDays(60))
            ->orderBy('due_date')
            ->get();

        $totals = [
            'total_billed'    => $rows->sum('billed'),
            'total_collected' => $rows->sum('collected'),
            'total_remaining' => $rows->sum('remaining'),
            'book_profit'     => $rows->sum('book_profit'),
            'earned_profit'   => $rows->sum('earned_profit'),
        ];

        return view('receivables.index', compact('rows', 'overdueInstallments', 'upcomingInstallments', 'totals'));
    }
}
