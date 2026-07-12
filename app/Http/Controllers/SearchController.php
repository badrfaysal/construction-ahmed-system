<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Support\ItemNameMatcher;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // Global search available from the topbar on every screen. One query hits
    // every entity the team looks things up by: projects, clients, suppliers,
    // materials/items, material returns, and supplier debts. All text matching
    // folds Arabic letter variants (أإآ→ا, ى→ي, ة→ه) on BOTH the term and the
    // columns (via ItemNameMatcher), so "احمد" finds "أحمد" and "ليلى" finds
    // "ليلي" — same normalization used by price tracking.
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $projects  = collect();
        $clients   = collect();
        $suppliers = collect();
        $items     = collect();
        $returns   = collect();
        $debts     = collect();

        if ($q !== '') {
            $needle = ItemNameMatcher::normalizeLetters($q);
            $like   = "%{$needle}%";

            // Reusable "column LIKE needle" that folds letter variants in SQL.
            $matches = fn (string $col) => [ItemNameMatcher::sqlNormalizeLetters($col) . ' LIKE ?', [$like]];

            [$nameSql, $bind]  = $matches('name');
            [$itemSql]         = $matches('item');
            [$notesSql]        = $matches('notes');
            [$descSql]         = $matches('description');

            // Projects — by project name or the client's name
            $projects = Project::with('client')
                ->whereRaw($nameSql, $bind)
                ->orWhereHas('client', fn ($c) => $c->whereRaw($nameSql, $bind))
                ->orderBy('name')
                ->get();

            // Clients — by name or phone (phone matched raw, digits need no fold)
            $clients = Client::whereRaw($nameSql, $bind)
                ->orWhere('phone', 'like', "%{$q}%")
                ->withCount('projects')
                ->orderBy('name')
                ->get();

            // Suppliers — by name
            $suppliers = Supplier::whereRaw($nameSql, $bind)
                ->orderBy('name')
                ->get();

            // Materials/items — grouped by fuzzy-normalized name for price history
            $materials = Material::whereRaw($itemSql, $bind)->get();
            $items = collect(ItemNameMatcher::group($materials, fn ($m) => $m->item))
                ->map(fn ($g) => (object) ['name' => $g['canonical'], 'count' => count($g['items'])])
                ->sortBy('name')
                ->values();

            // Material returns — by the returned item's name or the return note
            $returns = MaterialReturn::with(['material.project', 'material.supplier'])
                ->where(fn ($w) => $w
                    ->whereRaw($notesSql, $bind)
                    ->orWhereHas('material', fn ($m) => $m->whereRaw($itemSql, $bind)))
                ->latest('id')
                ->get();

            // Supplier debts — by description, note, or the supplier's name
            $debts = SupplierDebt::with(['supplier', 'project'])
                ->where(fn ($w) => $w
                    ->whereRaw($descSql, $bind)
                    ->orWhereRaw($notesSql, $bind)
                    ->orWhereHas('supplier', fn ($s) => $s->whereRaw($nameSql, $bind)))
                ->latest('id')
                ->get();
        }

        return view('search.index', compact(
            'q', 'projects', 'clients', 'suppliers', 'items', 'returns', 'debts'
        ));
    }
}
