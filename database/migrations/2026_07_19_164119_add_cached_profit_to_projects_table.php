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
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->decimal('cached_trade_profit', 15, 2)->default(0)->after('cached_spent');
            $table->decimal('cached_percentage_profit', 15, 2)->default(0)->after('cached_trade_profit');
            $table->decimal('cached_total_discount', 15, 2)->default(0)->after('cached_percentage_profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->dropColumn(['cached_trade_profit', 'cached_percentage_profit', 'cached_total_discount']);
        });
    }
};
