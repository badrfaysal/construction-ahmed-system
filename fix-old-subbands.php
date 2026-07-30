<?php
use Illuminate\Support\Facades\DB;

$mats = \App\Models\Material::where('category', 'misc')
    ->whereNull('band_worker_id')
    ->whereNull('supplier_id')
    ->whereNotNull('supplier_name')
    ->get();

$count = 0;
$missing = 0;

foreach($mats as $m) {
    // Attempt to find a worker in this project
    $worker = \App\Models\BandWorker::whereHas('band', function($q) use ($m) {
        $q->where('project_id', $m->project_id);
    })->where('name', $m->supplier_name)->first();
    
    if ($worker) {
        $m->update(['band_worker_id' => $worker->id]);
        $count++;
    } else {
        // Find if this is a general Supplier
        $supplier = \App\Models\Supplier::where('name', $m->supplier_name)->first();
        if ($supplier) {
            $m->update(['supplier_id' => $supplier->id]);
            $count++;
        } else {
            // It could be an unregistered worker or supplier.
            // Based on user feedback, they were technicians, so we should create a BandWorker.
            $worker = \App\Models\BandWorker::create([
                'project_band_id' => $m->band_id,
                'name' => $m->supplier_name,
                'contract_type' => $m->contract_type ?: 'lump_sum',
                'contract_qty' => $m->qty,
                'contract_unit_rate' => $m->unit_price,
                'amount' => $m->qty * $m->unit_price,
                'sell_rate' => $m->sell_price,
                'sell_amount' => $m->qty * ($m->sell_price ?? $m->unit_price),
                'supervision_pct' => $m->supervision_pct ?? 0,
                'start_date' => $m->date,
                'sort_order' => 99,
            ]);
            // Recompute band labor amount
            $band = \App\Models\ProjectBand::find($m->band_id);
            if ($band) {
                $band->update([
                    'labor_amount' => $band->workers()->sum('amount'),
                    'labor_sell_price' => $band->workers->sum(fn ($w) => $w->clientPrice()),
                    'labor_supervision_pct' => 0,
                ]);
            }

            $m->update(['band_worker_id' => $worker->id]);
            $count++;
            $missing++;
        }
    }
}

echo "Migrated $count records. (Created $missing missing workers)\n";
