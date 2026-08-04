<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Settings::current();
        $accounts = Account::orderBy('id')->get();
        $users = User::orderBy('id')->get();
        return view('settings.edit', compact('settings', 'accounts', 'users'));
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:bank_wallet,safe_cash,project_sector'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $account = Account::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'initial_balance' => $data['initial_balance'],
                'balance' => 0, // TransactionObserver will increment this
                'status' => 'active',
            ]);

            if ($data['initial_balance'] > 0) {
                // Record the initial balance as a manual transaction so it shows in the wallet history
                Transaction::create([
                    'project_id'  => null,
                    'band_id'     => null,
                    'account_id'  => $account->id,
                    'direction'   => 'in',
                    'type'        => 'رصيد افتتاحي',
                    'party'       => 'رصيد افتتاحي',
                    'amount'      => $data['initial_balance'],
                    'date'        => now(),
                    'description' => 'رصيد بداية المدة',
                    'ref_type'    => 'manual',
                    'ref_id'      => null,
                ]);
            }
        });

        return back()->with('success', 'تم إضافة الحساب بنجاح.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name'            => ['required', 'string', 'max:255'],
            'company_tagline'         => ['nullable', 'string', 'max:255'],
            'company_phone'           => ['nullable', 'string', 'max:50'],
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

        try {
            // Provide SystemRoot explicitly to avoid Winsock initialization errors (10106) under Apache/XAMPP
            $result = \Illuminate\Support\Facades\Process::env([
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            ])->run($command);

            if ($result->failed()) {
                \Illuminate\Support\Facades\Log::error("Database backup failed", ['output' => $result->errorOutput() ?: $result->output()]);
                return back()->with('error', 'حدث خطأ أثناء تصدير قاعدة البيانات. يرجى التأكد من توفر أداة mysqldump.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Database backup exception", ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تصدير قاعدة البيانات: الوظائف المطلوبة معطلة في إعدادات الخادم (exec/proc_open).');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function resetDatabase(Request $request)
    {
        if ($request->input('confirm_text') !== 'تصفير') {
            return back()->with('error', 'كلمة التأكيد غير صحيحة. لم يتم تصفير النظام.');
        }

        $tables = [
            'sy2_audit_logs',
            'sy2_transactions',
            'sy2_installment_payments',
            'sy2_installment_contracts',
            'sy2_worker_payments',
            'sy2_band_workers',
            'sy2_material_returns',
            'sy2_materials',
            'sy2_material_invoices',
            'sy2_installments',
            'sy2_supplier_debts',
            'sy2_warranty_complaints',
            'sy2_warranties',
            'sy2_quote_band_workers',
            'sy2_quote_band_items',
            'sy2_quote_bands',
            'sy2_quotes',
            'sy2_project_discounts',
            'sy2_project_bands',
            'sy2_projects',
            'sy2_marketers',
            'sy2_clients',
            'sy2_suppliers',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        // Reset accounts but keep default
        DB::table('sy2_accounts')->where('id', '!=', Account::WALLET_ID)->delete();
        DB::table('sy2_accounts')->where('id', Account::WALLET_ID)->update(['balance' => 0, 'initial_balance' => 0]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return redirect()->route('dashboard')->with('success', 'تم تصفير قاعدة البيانات بنجاح.');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:sy2_system_users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,viewer'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->has('is_active');
        $data['hide_financials'] = $request->has('hide_financials');

        User::create($data);

        return back()->with('success', 'تم إضافة المستخدم بنجاح.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:sy2_system_users,username,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,viewer'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        $data['is_active'] = $request->has('is_active');
        $data['hide_financials'] = $request->has('hide_financials');

        $user->update($data);

        return back()->with('success', 'تم تحديث بيانات المستخدم.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
