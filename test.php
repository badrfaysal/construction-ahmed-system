<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Project::find(3);
$gross = $p->bands->sum(fn($b) => $b->computeActualClientTotal()) + $p->generalMaterials()->sum(fn($m) => $m->netClientCost());
echo "Gross: " . $gross . "\n";
