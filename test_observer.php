<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$e = App\Models\Expense::make(['amount' => 100, 'description' => 'سيارة', 'account_id' => 1, 'date' => now()]);
$e->save();
echo $e->description;
$e->delete();
