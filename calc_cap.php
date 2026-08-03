<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new App\Services\CapitalService();
echo json_encode($c->calculateCurrentCapital(), JSON_PRETTY_PRINT);
