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
        Schema::table('sy2_supplier_debts', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('material_id')->constrained('sy2_material_invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sy2_supplier_debts', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
