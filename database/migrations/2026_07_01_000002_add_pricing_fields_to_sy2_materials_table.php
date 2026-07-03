<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// unit_price keeps meaning "purchase price" (our cost). These two add the
// client-facing side: what we charge the client per unit, plus a supervision
// markup — so the client statement can show a real sale price, not our cost.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->decimal('sell_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('supervision_pct', 5, 2)->default(0)->after('sell_price');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn(['sell_price', 'supervision_pct']);
        });
    }
};
