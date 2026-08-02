<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Account;

// 1. Update wallet balance
$account = Account::find(37);
$account->balance += 5000;
$account->save();
echo "Wallet updated. New balance: " . $account->balance . "\n";

// 2. Create audit logs for transactions 63 and 64
foreach([63, 64] as $id) {
    $tx = Transaction::find($id);
    if($tx && !AuditLog::where('transaction_id', $id)->exists()) {
        AuditLog::create([
            'action'         => 'created',
            'transaction_id' => $tx->id,
            'direction'      => $tx->direction,
            'type'           => $tx->type,
            'party'          => $tx->party,
            'amount'         => $tx->amount,
            'project_id'     => $tx->project_id,
            'band_id'        => $tx->band_id,
            'account_id'     => $tx->account_id,
            'ref_type'       => $tx->ref_type,
            'ref_id'         => $tx->ref_id,
            'description'    => $tx->description,
            'date'           => $tx->date,
            'performed_by'   => 1, // Assume Admin
            'happened_at'    => $tx->created_at,
        ]);
        echo "Created audit log for tx $id\n";
    }
}
echo "Done.\n";
