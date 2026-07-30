<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaterialInvoice;
use App\Models\Material;
use App\Models\SupplierDebt;

echo "Cleaning up broken debts...\n";
SupplierDebt::whereNull('invoice_id')->whereNull('material_id')->delete();

echo "Re-syncing material debts...\n";
foreach (Material::whereNull('invoice_id')->get() as $material) {
    event('eloquent.updated: App\Models\Material', $material);
}

echo "Re-syncing invoice debts...\n";
foreach (MaterialInvoice::all() as $invoice) {
    event('eloquent.updated: App\Models\MaterialInvoice', $invoice);
}

echo "Done.\n";
