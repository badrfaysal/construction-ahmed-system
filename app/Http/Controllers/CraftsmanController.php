<?php

namespace App\Http\Controllers;

use App\Models\BandWorker;
use App\Support\ItemNameMatcher;

// Unified craftsmen (الصنايعية) directory: the same person shows up as a
// separate sy2_band_workers row on every band he works. This screen fuzzy-
// groups those rows back into one person so you can see, across ALL projects,
// what he's contracted for, what he's been paid, and — most importantly — what
// he's still owed right now (مستحق). Sorted by who we owe the most.
class CraftsmanController extends Controller
{
    public function index()
    {
        $workers = BandWorker::with(['payments', 'band.project'])->get();

        $craftsmen = collect(ItemNameMatcher::group($workers, fn ($w) => $w->name))
            ->map(function ($group) {
                $rows = collect($group['items']);

                return (object) [
                    'name'        => $group['canonical'],
                    'phones'      => $rows->pluck('phone')->filter()->unique()->values(),
                    'specialties' => $rows->pluck('specialty')->filter()->unique()->values(),
                    'projects'    => $rows->pluck('band.project.name')->filter()->unique()->count(),
                    'contracted'  => (float) $rows->sum(fn ($w) => (float) $w->amount),
                    'paid'        => (float) $rows->sum(fn ($w) => $w->paidTotal()),
                    'remaining'   => (float) $rows->sum(fn ($w) => $w->remaining()),
                    // One line per band assignment, newest-worked first
                    'assignments' => $rows->sortByDesc(fn ($w) => $w->start_date)->values(),
                ];
            })
            ->sortByDesc('remaining')
            ->values();

        $totalRemaining = $craftsmen->sum('remaining');
        $totalPaid      = $craftsmen->sum('paid');

        return view('craftsmen.index', compact('craftsmen', 'totalRemaining', 'totalPaid'));
    }
}
