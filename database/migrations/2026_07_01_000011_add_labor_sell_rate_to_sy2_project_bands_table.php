<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The client-facing counterpart to contract_unit_rate (the cost rate) — lets
// labor_sell_price auto-scale with contract_qty just like labor_amount does,
// instead of being a flat number decoupled from the number of days/meters.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->decimal('labor_sell_rate', 12, 2)->nullable()->after('contract_unit_rate');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->dropColumn('labor_sell_rate');
        });
    }
};
