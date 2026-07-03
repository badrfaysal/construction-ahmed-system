<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Price tracking is now derived automatically from real sy2_materials
// purchases (via ItemNameMatcher fuzzy grouping) instead of manual entry —
// this table (currently empty) is no longer needed.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sy2_price_history');
    }

    public function down(): void
    {
        Schema::create('sy2_price_history', function ($table) {
            $table->id();
            $table->string('item_name');
            $table->string('period', 20);
            $table->decimal('price', 10, 2);
            $table->timestamps();
            $table->unique(['item_name', 'period']);
            $table->index('item_name');
        });
    }
};
