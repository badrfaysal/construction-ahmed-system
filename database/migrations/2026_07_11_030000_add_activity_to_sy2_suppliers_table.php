<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_suppliers', function (Blueprint $table) {
            $table->string('activity', 255)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_suppliers', function (Blueprint $table) {
            $table->dropColumn('activity');
        });
    }
};
