<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \App\Models\SystemActivityLog::where('action', 'updated')->where('model_type', 'App\Models\User')->orderBy('id', 'desc')->take(20)->get();
foreach($logs as $log) {
    echo "ID: " . $log->id . "\n";
    echo "Desc: " . $log->getTelegramDescription() . "\n";
    echo "Old: " . json_encode($log->old_values) . "\n";
    echo "New: " . json_encode($log->new_values) . "\n";
    echo "------------------\n";
}
