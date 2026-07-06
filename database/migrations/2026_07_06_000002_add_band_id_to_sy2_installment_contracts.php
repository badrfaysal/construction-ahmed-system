<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ربط عقد التقسيط ببند محدد (اختياري) — عشان نعرف إن بند بعينه اتحوّل لعقد
// تقسيط (يظهر في المستحقات "محوّل لعقد تقسيط"). null = العقد للمشروع كامل.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_installment_contracts', function (Blueprint $table) {
            $table->foreignId('band_id')->nullable()->after('project_id')
                ->constrained('sy2_project_bands')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sy2_installment_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('band_id');
        });
    }
};
