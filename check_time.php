<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$l = \App\Models\SystemActivityLog::find(579);
echo "Raw created_at in DB: " . $l->getRawOriginal('created_at') . PHP_EOL;

