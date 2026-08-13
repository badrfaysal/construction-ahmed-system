<?php
$logs = \App\Models\AuditLog::latest('id')->take(5)->get();
foreach($logs as $log) {
    echo $log->happened_at . ' | Tx: ' . $log->transaction_id . ' | Ref: ' . $log->ref_type . "\n";
}
