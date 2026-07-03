<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Financial transactions ledger — every money-in and money-out event.
// Can be linked to an installment, material purchase, or labor payment via
// ref_type + ref_id, or left as a manual standalone entry.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('sy2_projects')->nullOnDelete();
            $table->foreignId('band_id')->nullable()->constrained('sy2_project_bands')->nullOnDelete();
            // in = money received (client payment), out = money spent (materials, labor)
            $table->enum('direction', ['in', 'out']);
            // broad category to group the ledger — e.g. 'client_payment', 'labor', 'materials', 'other'
            $table->string('type', 100);
            $table->string('party');        // who the money came from or went to
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('description')->nullable();
            // optional soft link to the record that generated this transaction
            $table->string('ref_type', 50)->nullable();   // 'installment', 'material', 'labor'
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('band_id');
            $table->index('direction');
            $table->index('date');
            $table->index('type');
            // composite index speeds up "show all transactions for project X, newest first"
            $table->index(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_transactions');
    }
};
