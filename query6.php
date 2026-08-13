<?php
$logs = \App\Models\AuditLog::latest('id')->take(10)->get();
foreach($logs as $log) {
    echo $log->id . ' | Tx: ' . $log->transaction_id . ' | Ref: ' . $log->ref_type . ' | Time: ' . $log->happened_at . "\n";
}
