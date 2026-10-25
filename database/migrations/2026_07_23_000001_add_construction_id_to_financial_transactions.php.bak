<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// يضيف عمود construction_id على جدول financial_transactions المشترك — عشان نقدر
// نجمّع كل الحركات المالية اللي تخص المقاولات (من أي حساب/محفظة) ونحسبها في
// معادلة رأس المال بدل ما كنا بنعتمد على رصيد محفظة واحدة (id=37) فقط.
//
// العمود nullable عشان السيستم الأول مش بيستخدمه — backward-compatible تمامًا.
// الحركات اليدوية (تغذية رأس مال / مسحوبات / مصاريف إدارية) مش بتاخد construction_id
// لأنها مش مرتبطة بمشاريع.
return new class extends Migration
{
    public function up(): void
    {
        // 1) إضافة العمود + Index
        if (! Schema::hasColumn('financial_transactions', 'construction_id')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('construction_id')->nullable()->after('ref_type')
                    ->comment('ID الحركة من sy2_transactions — للتجميع في معادلة رأس المال');
                $table->index('construction_id', 'idx_ft_construction_id');
            });
        }

        // 2) Backfill: كل الصفوف الموجودة اللي ref_type = 'construction' و status مش cancelled
        //    تاخد construction_id = ref_id (لأن ref_id هو أصلاً id الحركة من sy2_transactions)
        //    بس فقط اللي ليها ref_type مش manual (الحركات المرتبطة بمشاريع)
        DB::statement("
            UPDATE financial_transactions
            SET construction_id = ref_id
            WHERE ref_type = 'construction'
              AND (status IS NULL OR status != 'cancelled')
              AND ref_id IS NOT NULL
              AND ref_id IN (
                  SELECT id FROM sy2_transactions WHERE ref_type != 'manual' OR ref_type IS NULL
              )
        ");
    }

    public function down(): void
    {
        if (Schema::hasColumn('financial_transactions', 'construction_id')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_ft_construction_id');
                $table->dropColumn('construction_id');
            });
        }
    }
};
