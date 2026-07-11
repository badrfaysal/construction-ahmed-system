<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional itemized labor breakdown of a quote band — mirrors sy2_band_workers
// so a quote's technicians carry over as-is into real BandWorker rows when the
// quote is converted into a project (see QuoteController::convertToProject()).
// No phone/start_date here: a quote is just an estimate, the craftsman isn't
// actually booked yet.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_quote_band_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_band_id')->constrained('sy2_quote_bands')->cascadeOnDelete();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->enum('contract_type', ['lump_sum', 'daily', 'per_meter', 'per_piece'])->nullable();
            $table->decimal('contract_qty', 10, 2)->nullable();
            $table->decimal('contract_unit_rate', 12, 2)->nullable();
            $table->decimal('sell_rate', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('sell_amount', 12, 2)->default(0);
            $table->decimal('supervision_pct', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index('quote_band_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_quote_band_workers');
    }
};
