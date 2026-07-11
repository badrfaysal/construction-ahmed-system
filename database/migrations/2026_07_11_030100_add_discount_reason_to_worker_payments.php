<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_worker_payments', function (Blueprint $table) {
            $table->string('discount_reason', 500)->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_worker_payments', function (Blueprint $table) {
            $table->dropColumn('discount_reason');
        });
    }
};
