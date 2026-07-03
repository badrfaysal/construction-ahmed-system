<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same per-item supervision markup already used on materials/labor — applied
// to quote items too so the quoted price already reflects the real margin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_quote_band_items', function (Blueprint $table) {
            $table->decimal('supervision_pct', 5, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_quote_band_items', function (Blueprint $table) {
            $table->dropColumn('supervision_pct');
        });
    }
};
