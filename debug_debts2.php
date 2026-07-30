<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SupplierDebt;

$debts = SupplierDebt::all();
foreach ($debts as $debt) {
    echo "Debt ID: " . $debt->id . "\n";
    echo "  Total: " . $debt->total_amount . "\n";
    echo "  Paid: " . $debt->paid_amount . "\n";
    echo "  Invoice ID: " . $debt->invoice_id . "\n";
    echo "  Material ID: " . $debt->material_id . "\n";
}
echo "Done.\n";
