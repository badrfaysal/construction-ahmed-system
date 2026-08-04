<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mdebts = \App\Models\ManualDebt::all();
$mgrouped = $mdebts->groupBy('party');
echo "Manual Debts:\n";
foreach($mgrouped as $party => $group) {
    $remaining = $group->sum(function($d) { return $d->remaining(); });
    $statuses = $group->pluck('status')->unique()->toArray();
    $allPaid = $group->every(function($r) { return $r->status === 'paid'; });
    echo "Party $party - Remaining: $remaining - Statuses: " . implode(', ', $statuses) . " - allPaid: " . ($allPaid ? 'YES' : 'NO') . "\n";
}

$debts = \App\Models\SupplierDebt::all();
$grouped = $debts->groupBy('supplier_id');
echo "\nSupplier Debts:\n";
foreach($grouped as $sid => $group) {
    $remaining = $group->sum(function($d) { return $d->remaining(); });
    $statuses = $group->pluck('status')->unique()->toArray();
    echo "Supplier $sid - Remaining: $remaining - Statuses: " . implode(', ', $statuses) . "\n";
}
