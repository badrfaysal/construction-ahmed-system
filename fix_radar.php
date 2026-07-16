<?php
$materials = App\Models\Material::whereNotIn('id', function($q) {
    $q->select('ref_id')->from('sy2_transactions')->where('ref_type', 'material');
})->get();

foreach ($materials as $m) {
    App\Models\Transaction::create([
        'project_id'  => $m->project_id,
        'band_id'     => $m->band_id,
        'account_id'  => $m->account_id,
        'direction'   => 'out',
        'type'        => 'شراء مواد',
        'party'       => $m->supplier?->name ?? $m->item,
        'amount'      => 0, // It was deferred
        'date'        => $m->date,
        'description' => $m->item . ' — ' . number_format($m->qty, 1) . ' ' . $m->unit,
        'ref_type'    => 'material',
        'ref_id'      => $m->id,
    ]);
}

$returns = App\Models\MaterialReturn::whereNotIn('id', function($q) {
    $q->select('ref_id')->from('sy2_transactions')->where('ref_type', 'return');
})->get();

foreach ($returns as $r) {
    $m = $r->material;
    if ($m) {
        App\Models\Transaction::create([
            'project_id'  => $m->project_id,
            'band_id'     => $m->band_id,
            'direction'   => 'in',
            'type'        => 'مرتجع خامات',
            'party'       => $m->supplier?->name ?? $m->item,
            'amount'      => 0, // It was deferred
            'date'        => $r->date,
            'description' => 'مرتجع ' . $m->item . ' — ' . number_format($r->qty, 1) . ' ' . $m->unit,
            'ref_type'    => 'return',
            'ref_id'      => $r->id,
        ]);
    }
}
echo "Fixed " . $materials->count() . " materials and " . $returns->count() . " returns.\n";
