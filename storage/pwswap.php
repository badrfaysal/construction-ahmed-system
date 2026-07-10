<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@aldabaa.com')->first();
file_put_contents(
    'C:/Users/i7/AppData/Local/Temp/claude/D--Projects-Construction-ahmed-system-Construction-ahmed-system/87c81c24-0c7c-4a31-b547-2848de426c96/scratchpad/admin_hash_backup.txt',
    $admin->password
);
$admin->password = Hash::make('PreviewTemp!2026xk');
$admin->save();
echo "swapped\n";
