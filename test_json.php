<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = \App\Models\AuditLog::latest('id')->take(5)->get();
foreach($logs as $log) {
    $logData = [
        'id' => $log->id,
        'action_type' => $log->action,
        'old_values' => is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values
    ];
    $json = json_encode($logData);
    echo $log->id . ": " . ($json === false ? "FAILED" : "OK: " . substr($json, 0, 100)) . "\n";
}
