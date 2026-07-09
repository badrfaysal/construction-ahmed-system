<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sy2_transactions', function (Blueprint $table) {
            // خصم اختياري مرتبط بالتحصيل — بيقلل المستحق زي المبلغ المدفوع بالظبط
            // بس من غير ما يدخل المحفظة (نفس فكرة discount_applied في الأقساط)
            $table->decimal('discount', 12, 2)->nullable()->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_transactions', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
