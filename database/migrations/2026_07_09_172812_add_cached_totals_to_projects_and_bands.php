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
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->decimal('cached_actual_total', 14, 2)->default(0)->after('labor_supervision_pct');
            $table->decimal('cached_total_cost', 14, 2)->default(0)->after('cached_actual_total');
        });

        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->decimal('cached_actual_total', 14, 2)->default(0)->after('notes');
            $table->decimal('cached_collected', 14, 2)->default(0)->after('cached_actual_total');
            $table->decimal('cached_spent', 14, 2)->default(0)->after('cached_collected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->dropColumn(['cached_actual_total', 'cached_total_cost']);
        });

        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->dropColumn(['cached_actual_total', 'cached_collected', 'cached_spent']);
        });
    }
};
