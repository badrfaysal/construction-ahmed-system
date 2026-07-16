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
        Schema::create('sy2_material_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('sy2_suppliers')->nullOnDelete();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->date('date');
            $table->string('name')->nullable(); // optional invoice name / reference
            $table->decimal('total_amount', 12, 2)->default(0); // gross
            $table->decimal('paid_amount', 12, 2)->default(0); // what was paid in cash
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('supplier_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_material_invoices');
    }
};
