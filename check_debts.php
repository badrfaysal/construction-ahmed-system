<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$debts = \App\Models\SupplierDebt::all();
$grouped = $debts->groupBy('supplier_id');
echo "Supplier Debts:\n";
foreach($grouped as $sid => $group) {
    $remaining = $group->sum(function($d) { return $d->remaining(); });
    if ($remaining <= 0.01) echo "Supplier $sid is fully paid.\n";
    else echo "Supplier $sid is ACTIVE (Remaining: $remaining).\n";
}

$mdebts = \App\Models\ManualDebt::all();
$mgrouped = $mdebts->groupBy('party');
echo "\nManual Debts:\n";
foreach($mgrouped as $party => $group) {
    $remaining = $group->sum(function($d) { return $d->remaining(); });
    if ($remaining <= 0.01) echo "Party $party is fully paid.\n";
    else echo "Party $party is ACTIVE (Remaining: $remaining).\n";
}
