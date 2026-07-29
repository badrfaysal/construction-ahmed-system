<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$columns = Illuminate\Support\Facades\DB::select('DESCRIBE accounts');
print_r($columns);
$rows = Illuminate\Support\Facades\DB::select('SELECT * FROM accounts LIMIT 5');
print_r($rows);
