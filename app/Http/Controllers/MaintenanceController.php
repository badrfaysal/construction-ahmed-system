<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// أدوات صيانة/تجارب — تصفير بيانات نظام المقاولات فقط.
// خطر: يمسح كل بيانات الشغل (مشاريع، خامات، حركات...). للتيست بس.
// admin-only (مقفول من الراوت عبر middleware role:admin).
class MaintenanceController extends Controller
{
    // جداول الدومين بتاعة المقاولات اللي بتتفرّغ عند التصفير.
    // مرتّبة من الأبناء للآباء عشان الـ FK — بس إحنا بنقفل فحص الـ FK
    // أثناء العملية فالترتيب مش حرج، سايبينه واضح للقراءة.
    // ملاحظة: sy2_settings و sy2_users وجداول الفريمورك (users/sessions...)
    // مش بتتلمس إطلاقًا، وكمان الجداول غير المبدوءة بـ sy2_ بتاعة النظام التاني.
    // لازم يتحدّث كل ما يتضاف/يتشال جدول دومين جديد (آخر مرة اتراجعت
    // 2026-07-10 مقابل SHOW TABLES الفعلي) — عشان كده resetDatabase() تحت
    // بتتجاهل أي اسم مش موجود بدل ما تفشل بالكامل لو الليست فضلت متأخرة.
    private const TABLES = [
        'sy2_audit_logs',
        'sy2_transactions',
        'sy2_installment_payments',
        'sy2_installment_contracts',
        'sy2_worker_payments',
        'sy2_band_workers',
        'sy2_material_returns',
        'sy2_materials',
        'sy2_installments',
        'sy2_supplier_debts',
        'sy2_warranty_complaints',
        'sy2_warranties',
        'sy2_quote_band_items',
        'sy2_quote_bands',
        'sy2_quotes',
        'sy2_project_bands',
        'sy2_projects',
    ];

    public function resetDatabase()
    {
        // نقفل فحص الـ FK مؤقتًا عشان الـ truncate ما يقفش على العلاقات
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (self::TABLES as $table) {
                // تجاهل أي جدول اتشال من الـ schema بدل ما تفشل العملية كلها بسببه
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // نرجّع رصيد محفظة المقاولات لصفر
        DB::table((new Account)->getTable())
            ->where('id', Account::WALLET_ID)
            ->update(['balance' => 0]);

        return back()->with('success', 'تم تصفير بيانات المقاولات بالكامل (المشاريع، الخامات، الحركات، المحفظة...). النظام جاهز لتيست جديد.');
    }
}
