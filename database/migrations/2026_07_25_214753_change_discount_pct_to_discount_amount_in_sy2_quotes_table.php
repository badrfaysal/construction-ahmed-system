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
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->dropColumn('discount_pct');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
            $table->decimal('discount_pct', 5, 2)->default(0)->after('tax_pct');
        });
    }
};
