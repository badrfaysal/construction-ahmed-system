<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;

$txs = Transaction::where('account_id', 37)->orderBy('id')->get();
foreach($txs as $tx) {
    echo $tx->id . " | " . $tx->direction . " | " . $tx->amount . "\n";
}
