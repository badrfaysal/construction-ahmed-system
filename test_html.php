<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = view('radar.index', [
    'logs' => \App\Models\AuditLog::latest('id')->take(1)->get(), 
    'wallets' => [], 
    'liveIds' => [], 
    'projects' => [], 
    'projectsQuery' => \App\Models\Project::query(),
    'totalIn'=>0, 'totalOut'=>0, 'totalDiscount'=>0, 'totalTransfers'=>0, 'canceledCount'=>0
])->render();

preg_match('/data-log="(.*?)"/', $html, $m);
file_put_contents('test_data_log.txt', htmlspecialchars_decode($m[1], ENT_QUOTES));
echo "DONE\n";
