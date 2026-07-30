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
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('band_worker_id')->nullable()->after('band_id');
            $table->text('notes')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn(['band_worker_id', 'notes']);
        });
    }
};
