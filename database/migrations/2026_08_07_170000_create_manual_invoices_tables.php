<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_manual_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('client_name');
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();
            $table->date('date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_pct', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('sy2_manual_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_invoice_id')->constrained('sy2_manual_invoices')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('description');
            $table->decimal('qty', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_manual_invoice_items');
        Schema::dropIfExists('sy2_manual_invoices');
    }
};
