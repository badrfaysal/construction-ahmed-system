<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Settings::current();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name'            => ['required', 'string', 'max:255'],
            'company_tagline'         => ['nullable', 'string', 'max:255'],
            'company_phone'           => ['nullable', 'string', 'max:100'],
            'company_registration'    => ['nullable', 'string', 'max:100'],
            'whatsapp_country_code'   => ['required', 'string', 'max:5'],
        ]);

        Settings::current()->update($data);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }

    public function exportDatabase()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbName = config('database.connections.mysql.database');
        $dbPort = config('database.connections.mysql.port');

        $fileName = 'backup_' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path('app/' . $fileName);

        $mysqldumpPath = file_exists('C:\\xampp\\mysql\\bin\\mysqldump.exe') 
            ? '"C:\\xampp\\mysql\\bin\\mysqldump.exe"' 
            : 'mysqldump';

        $passArg = $dbPass ? "-p\"{$dbPass}\"" : "";
        $command = "{$mysqldumpPath} -h {$dbHost} -P {$dbPort} -u {$dbUser} {$passArg} {$dbName} > \"{$filePath}\" 2>&1";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            \Illuminate\Support\Facades\Log::error("Database backup failed", ['output' => $output]);
            return back()->with('error', 'حدث خطأ أثناء تصدير قاعدة البيانات. يرجى التأكد من توفر أداة mysqldump.');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
