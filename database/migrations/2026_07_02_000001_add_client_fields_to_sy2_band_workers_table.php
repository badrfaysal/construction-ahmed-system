<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Every band's labor is now always itemized as workers (no more band-level
// simple team_name path) — each worker needs a phone, their own client-facing
// sell price/rate + supervision %, a contract start date, and notes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_band_workers', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('specialty');
            $table->decimal('sell_rate', 12, 2)->nullable()->after('contract_unit_rate');
            $table->decimal('sell_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('supervision_pct', 5, 2)->default(0)->after('sell_amount');
            $table->date('start_date')->nullable()->after('supervision_pct');
            $table->text('notes')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_band_workers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'sell_rate', 'sell_amount', 'supervision_pct', 'start_date', 'notes']);
        });
    }
};
