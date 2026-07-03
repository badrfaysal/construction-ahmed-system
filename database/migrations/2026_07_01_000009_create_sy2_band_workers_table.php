<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional itemized breakdown of a band's labor: more than one technician can
// work a single band, each with their own specialty, contract type, and wage.
// When present, these drive the band's overall labor_amount (see ProjectBandController).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_band_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_band_id')->constrained('sy2_project_bands')->cascadeOnDelete();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->enum('contract_type', ['lump_sum', 'daily', 'per_meter'])->nullable();
            $table->decimal('contract_qty', 10, 2)->nullable();
            $table->decimal('contract_unit_rate', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index('project_band_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_band_workers');
    }
};
