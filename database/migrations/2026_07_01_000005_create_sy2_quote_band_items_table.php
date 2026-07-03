<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional itemized breakdown inside a quote band (e.g. band "تشطيب" listing
// the expected items with a rough qty/price each), so the quote shows detail
// instead of a single lump price. The band's price stays the sum of these.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_quote_band_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_band_id')->constrained('sy2_quote_bands')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index('quote_band_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_quote_band_items');
    }
};
