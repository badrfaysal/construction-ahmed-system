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
        Schema::table('sy2_settings', function (Blueprint $table) {
            $table->json('expense_categories')->nullable()->after('whatsapp_country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_settings', function (Blueprint $table) {
            //
        });
    }
};
