<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_manual_invoices', function (Blueprint $table) {
            $table->string('tax_number')->nullable()->after('tax_amount');
            $table->string('commercial_register')->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_manual_invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_number', 'commercial_register']);
        });
    }
};
