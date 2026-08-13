<?php
$logs = \App\Models\AuditLog::where('transaction_id', 183)->get();
foreach($logs as $log) {
    echo $log->action . ' - ref_type: ' . $log->ref_type . ' - amount: ' . $log->amount . ' - old_values: ' . json_encode($log->old_values) . "\n";
}
