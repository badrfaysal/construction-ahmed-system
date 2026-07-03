<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds:
//  1. band_id on sy2_installments — lets the user tag which work phase a payment covers
//  2. payment_status / paid_amount on sy2_materials — tracks partial / deferred purchases
//  3. sy2_supplier_debts — amounts we owe suppliers from partial or deferred payments
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tag each client payment with the band it relates to (optional)
        Schema::table('sy2_installments', function (Blueprint $table) {
            $table->foreignId('band_id')
                  ->nullable()
                  ->after('project_id')
                  ->constrained('sy2_project_bands')
                  ->nullOnDelete();
        });

        // 2. Track how a material purchase was paid
        Schema::table('sy2_materials', function (Blueprint $table) {
            // paid = settled in full, partial = part paid now, deferred = nothing paid yet
            $table->enum('payment_status', ['paid', 'partial', 'deferred'])
                  ->default('paid')
                  ->after('date');
            // Amount actually paid now (relevant for partial); full cost stored separately via netCost()
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
        });

        // 3. Supplier debts — what we still owe for partial / fully-deferred purchases
        Schema::create('sy2_supplier_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->foreignId('band_id')->nullable()->constrained('sy2_project_bands')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('sy2_suppliers')->nullOnDelete();
            // Link back to the originating material row (null for manually-entered debts)
            $table->foreignId('material_id')->nullable()->constrained('sy2_materials')->nullOnDelete();
            $table->string('description');          // what we owe for
            $table->decimal('total_amount', 12, 2); // original debt amount
            $table->decimal('paid_amount', 12, 2)->default(0); // how much we've paid off
            $table->date('due_date')->nullable();    // when this debt is due
            // pending = not paid yet, partial = partially paid, paid = fully settled
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_supplier_debts');

        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount']);
        });

        Schema::table('sy2_installments', function (Blueprint $table) {
            $table->dropForeign(['band_id']);
            $table->dropColumn('band_id');
        });
    }
};
