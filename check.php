<?php
$mats = \App\Models\Material::where('category', 'misc')
    ->whereNull('band_worker_id')
    ->whereNull('supplier_id')
    ->whereNotNull('supplier_name')
    ->get();

$res = [];
foreach($mats as $m) {
    $worker = \App\Models\BandWorker::whereHas('band', function($q) use ($m) {
        $q->where('project_id', $m->project_id);
    })->where('name', $m->supplier_name)->first();
    
    $res[] = [
        'id' => $m->id, 
        'item' => $m->item, 
        'supplier_name' => $m->supplier_name, 
        'found_worker_id' => $worker?->id
    ];
}
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
