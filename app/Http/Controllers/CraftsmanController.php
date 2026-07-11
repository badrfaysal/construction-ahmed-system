<?php

namespace App\Http\Controllers;

use App\Models\BandWorker;
use App\Models\Project;
use App\Support\ItemNameMatcher;

// Unified craftsmen (الصنايعية) directory: the same person shows up as a
// separate sy2_band_workers row on every band he works. This screen fuzzy-
// groups those rows back into one person so you can see, across ALL projects,
// what he's contracted for, what he's been paid, and — most importantly — what
// he's still owed right now (مستحق). Sorted by who we owe the most.
class CraftsmanController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = BandWorker::with(['payments', 'band.project']);

        // فلتر بالمشروع (الشقة) — بيقصر كل الصفوف (وبالتالي الإجماليات) على
        // شغل الصنايعي في المشروع ده بس، مش كل مشاريعه
        if ($pid = $request->get('project_id')) {
            $query->whereHas('band', fn ($q) => $q->where('project_id', $pid));
        }

        $workers = $query->get();

        // فلتر بالتخصص — بعد الجلب عشان نقارن بالقيمة المدخلة يدويًا (مفيش
        // enum ثابت للتخصصات)
        if ($specialty = $request->get('specialty')) {
            $workers = $workers->filter(fn ($w) => trim((string) $w->specialty) === $specialty);
        }

        $ratings = \App\Models\CraftsmanRating::all()->keyBy('craftsman_name');

        $craftsmen = collect(ItemNameMatcher::group($workers, fn ($w) => $w->name))
            ->map(function ($group) use ($ratings) {
                $rows = collect($group['items']);
                $ratingObj = $ratings->get($group['canonical']);

                return (object) [
                    'name'           => $group['canonical'],
                    'phones'         => $rows->pluck('phone')->filter()->unique()->values(),
                    'specialties'    => $rows->pluck('specialty')->filter()->unique()->values(),
                    'bands_worked'   => $rows->pluck('band.name')->filter()->unique()->values(),
                    'projects'       => $rows->pluck('band.project.name')->filter()->unique()->count(),
                    'contracted'     => (float) $rows->sum(fn ($w) => (float) $w->amount),
                    'paid'           => (float) $rows->sum(fn ($w) => $w->paidTotal()),
                    'remaining'      => (float) $rows->sum(fn ($w) => $w->remaining()),
                    'owed_to_us'     => (float) $rows->sum(fn ($w) => $w->owedToUs()),
                    'payments_count' => $rows->sum(fn ($w) => $w->payments->count()),
                    'start_date'     => $rows->min('start_date'),
                    'rating'         => $ratingObj?->rating ?? 0,
                    'notes'          => $ratingObj?->notes ?? '',
                    // One line per band assignment, newest-worked first
                    'assignments'    => $rows->sortByDesc(fn ($w) => $w->start_date)->values(),
                ];
            });

        $craftsmen = (match ($request->get('sort', 'remaining_desc')) {
            'paid_desc'       => $craftsmen->sortByDesc('paid'),
            'contracted_desc' => $craftsmen->sortByDesc('contracted'),
            'projects_desc'   => $craftsmen->sortByDesc('projects'),
            'name'            => $craftsmen->sortBy('name'),
            'rating_desc'     => $craftsmen->sortByDesc('rating'),
            'rating_asc'      => $craftsmen->sortBy('rating'),
            default           => $craftsmen->sortByDesc('remaining'),
        })->values();

        $totalRemaining = $craftsmen->sum('remaining');
        $totalPaid      = $craftsmen->sum('paid');

        $projects    = Project::orderBy('name')->get(['id', 'name']);
        $specialties = BandWorker::whereNotNull('specialty')->where('specialty', '!=', '')
            ->distinct()->orderBy('specialty')->pluck('specialty');

        return view('craftsmen.index', compact('craftsmen', 'totalRemaining', 'totalPaid', 'projects', 'specialties'));
    }

    public function rate(\Illuminate\Http\Request $request, string $name)
    {
        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'notes'  => 'nullable|string',
        ]);

        \App\Models\CraftsmanRating::updateOrCreate(
            ['craftsman_name' => $name],
            $validated
        );

        return redirect()->back()->with('success', 'تم حفظ التقييم بنجاح.');
    }
}
