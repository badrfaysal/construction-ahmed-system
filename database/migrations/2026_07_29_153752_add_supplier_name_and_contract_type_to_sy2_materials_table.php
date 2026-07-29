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
            $table->string('supplier_name')->nullable()->after('supplier_id');
            $table->string('contract_type')->nullable()->after('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn(['supplier_name', 'contract_type']);
        });
    }
};
