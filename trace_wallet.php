<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\Account;

$account = Account::find(37);
echo "Wallet Balance: " . $account->balance . "\n";

$txs = Transaction::where('account_id', 37)->orderBy('id')->get();
$calculated = 0;
foreach($txs as $tx) {
    if ($tx->direction === 'in') {
        $calculated += $tx->amount;
    } else {
        $calculated -= $tx->amount;
    }
}
echo "Calculated Balance from transactions: " . $calculated . "\n";
