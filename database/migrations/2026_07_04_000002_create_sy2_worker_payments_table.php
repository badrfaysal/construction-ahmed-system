<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Craftsmen (الصنايعية) are paid in installments (دفعات) as the work progresses,
// not one lump sum up front. Each payment is recorded here and hits محفظة
// المقاولات the moment it's actually paid (لحظي) — the worker's contracted
// amount is just the commitment, never auto-debited in full. A worker who does
// part of the job and leaves keeps only the دفعات they were actually paid.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_worker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('band_worker_id')->constrained('sy2_band_workers')->cascadeOnDelete();
            // Denormalized so transactions/wallet sync and filtering by project
            // don't need to join through the band every time.
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->foreignId('project_band_id')->constrained('sy2_project_bands')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('band_worker_id');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_worker_payments');
    }
};
