<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->decimal('tax_pct', 5, 2)->default(0)->after('note');
            $table->decimal('discount_pct', 5, 2)->default(0)->after('tax_pct');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->dropColumn(['tax_pct', 'discount_pct']);
        });
    }
};
