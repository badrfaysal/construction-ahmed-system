<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Support\ItemNameMatcher;

class PriceHistoryController extends Controller
{
    // Groups every material purchase ever recorded by fuzzy item name (so
    // "جردل بوية" / "جردل البويه" count as the same item — see ItemNameMatcher)
    // and shows each one's latest price and how it's trending. Fully automatic:
    // there's no manual entry, this is derived straight from real purchases.
    public function index(\Illuminate\Http\Request $request)
    {
        $materials = Material::orderBy('date')->orderBy('id')->get();
        $groups = ItemNameMatcher::group($materials, fn ($m) => $m->item);

        $items = collect($groups)->map(function ($g) {
            $purchases = collect($g['items'])->values();
            $latest    = $purchases->last();
            $previous  = $purchases->count() > 1 ? $purchases->slice(-2, 1)->first() : null;

            return (object) [
                'name'           => $g['canonical'],
                'variants'       => $g['variants'],
                'unit'           => $latest->unit,
                'latest_price'   => (float) $latest->unit_price,
                'latest_date'    => $latest->date,
                'change_pct'     => $previous ? round((($latest->unit_price - $previous->unit_price) / $previous->unit_price) * 100, 1) : null,
                'purchase_count' => $purchases->count(),
                'min_price'      => (float) $purchases->min('unit_price'),
                'max_price'      => (float) $purchases->max('unit_price'),
            ];
        });

        $items = (match ($request->get('sort', 'name')) {
            'price_desc'  => $items->sortByDesc('latest_price'),
            'price_asc'   => $items->sortBy('latest_price'),
            'count_desc'  => $items->sortByDesc('purchase_count'),
            default       => $items->sortBy('name'),
        })->values();

        return view('price-history.index', compact('items'));
    }

    // Full purchase history for one (fuzzy-matched) item — every real
    // purchase across all projects/suppliers, in chronological order
    public function show(string $itemName)
    {
        $materials = Material::with(['project', 'supplier'])->orderBy('date')->orderBy('id')->get();
        $groups = ItemNameMatcher::group($materials, fn ($m) => $m->item);

        $group = collect($groups)->first(fn ($g) => $g['canonical'] === $itemName);
        abort_if(! $group, 404);

        return view('price-history.show', [
            'itemName'  => $group['canonical'],
            'variants'  => $group['variants'],
            'purchases' => collect($group['items'])->values(),
        ]);
    }
}
