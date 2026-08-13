<?php
$logs = \App\Models\AuditLog::latest('id')->take(5)->get();
foreach($logs as $log) {
    echo $log->id . ' | Tx: ' . $log->transaction_id . ' | Ref: ' . $log->ref_type . ' | Amt: ' . $log->amount . ' | Action: ' . $log->action . ' | Old: ' . json_encode($log->old_values) . "\n";
}
