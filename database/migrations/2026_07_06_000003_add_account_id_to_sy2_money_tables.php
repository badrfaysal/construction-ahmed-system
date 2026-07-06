<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// يضيف account_id (المحفظة المختارة) لكل الجداول اللي بتحرّك فلوس. القيمة null
// معناها المحفظة الافتراضية (المقاولات id=37). مفيش foreign key لأن جدول
// accounts بتاع السيستم الأول ومش بنتحكم في schema بتاعه.
return new class extends Migration
{
    private array $tables = [
        'sy2_transactions',
        'sy2_materials',
        'sy2_worker_payments',
        'sy2_installment_contracts',
        'sy2_installment_payments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'account_id')) {
                continue;
            }
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedBigInteger('account_id')->nullable()->after('id')
                    ->comment('المحفظة المختارة من جدول accounts — null = المقاولات الافتراضية');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            if (! Schema::hasColumn($t, 'account_id')) {
                continue;
            }
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('account_id');
            });
        }
    }
};
