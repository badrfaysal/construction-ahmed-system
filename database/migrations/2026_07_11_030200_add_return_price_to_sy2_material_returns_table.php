<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_material_returns', function (Blueprint $table) {
            // نُل = يتساوى بسعر الشراء الأصلي (رجوع عادي بدون خسارة) — لو
            // اتحدد بسعر أقل، الفرق بيتحسب خسارة (شايف MaterialReturn::loss())
            $table->decimal('return_price', 12, 2)->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_material_returns', function (Blueprint $table) {
            $table->dropColumn('return_price');
        });
    }
};
