<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$band = App\Models\ProjectBand::where('sell_price', 3450)->with('project')->first();
echo json_encode($band->toArray(), JSON_PRETTY_PRINT);
