<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaterialInvoice;
use App\Models\SupplierDebt;

$invoice = MaterialInvoice::latest('id')->first();
echo "Invoice ID: " . $invoice->id . "\n";
echo "Total Amount: " . $invoice->total_amount . "\n";
echo "Paid Amount: " . $invoice->paid_amount . "\n";

$debt = SupplierDebt::where('invoice_id', $invoice->id)->first();
if ($debt) {
    echo "Debt ID: " . $debt->id . "\n";
    echo "Debt Total: " . $debt->total_amount . "\n";
    echo "Debt Paid: " . $debt->paid_amount . "\n";
} else {
    echo "No Debt Found.\n";
}

echo "Materials:\n";
foreach ($invoice->materials as $m) {
    echo " - Material ID: " . $m->id . ", Gross: " . $m->grossCost() . ", PaidRatio: " . $m->paidRatio() . "\n";
    foreach ($m->returns as $r) {
        echo "   - Return ID: " . $r->id . ", Qty: " . $r->qty . ", EffectivePrice: " . $r->effectivePrice() . "\n";
    }
}
