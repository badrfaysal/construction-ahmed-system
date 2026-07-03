<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// labor_amount keeps meaning "what we actually paid the team" (our cost).
// These two add the client-facing side for labor, mirroring the materials table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->decimal('labor_sell_price', 12, 2)->nullable()->after('labor_amount');
            $table->decimal('labor_supervision_pct', 5, 2)->default(0)->after('labor_sell_price');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->dropColumn(['labor_sell_price', 'labor_supervision_pct']);
        });
    }
};
