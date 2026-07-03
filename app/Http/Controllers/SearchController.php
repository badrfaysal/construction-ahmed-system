<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Project;
use App\Models\Supplier;
use App\Support\ItemNameMatcher;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // Global search available from the topbar on every screen — matches
    // projects (by name or client), suppliers, and materials/items (using the
    // same Arabic-aware normalization as price tracking, so partial/misspelled
    // item names still find the right results)
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $projects = collect();
        $suppliers = collect();
        $items = collect();

        if ($q !== '') {
            $projects = Project::with('client')
                ->where('name', 'like', "%{$q}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                ->orderBy('name')
                ->get();

            $suppliers = Supplier::where('name', 'like', "%{$q}%")
                ->orderBy('name')
                ->get();

            $materials = Material::where('item', 'like', "%{$q}%")->get();
            if ($materials->isEmpty()) {
                // Fall back to normalized matching for misspelled/variant names
                $materials = Material::all()->filter(fn ($m) => ItemNameMatcher::contains($m->item, $q));
            }
            $items = collect(ItemNameMatcher::group($materials, fn ($m) => $m->item))
                ->map(fn ($g) => (object) ['name' => $g['canonical'], 'count' => count($g['items'])])
                ->sortBy('name')
                ->values();
        }

        return view('search.index', compact('q', 'projects', 'suppliers', 'items'));
    }
}
