<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$band = App\Models\ProjectBand::find(17);
if ($band) {
    echo json_encode($band->toArray(), JSON_PRETTY_PRINT);
    echo "\nWorkers:\n";
    echo json_encode($band->workers->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "Band 17 not found.\n";
}
