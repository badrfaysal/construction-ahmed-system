<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
$tx = Transaction::whereIn('id', [67,68,69])->get();
foreach($tx as $t) {
    echo $t->id . " | " . $t->amount . "\n";
}
