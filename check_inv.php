<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$inv = App\Models\MaterialInvoice::orderBy('id', 'desc')->first();
echo json_encode($inv->toArray(), JSON_PRETTY_PRINT) . "\n";
$mats = App\Models\Material::where('invoice_id', $inv->id)->get();
echo json_encode($mats->toArray(), JSON_PRETTY_PRINT) . "\n";
