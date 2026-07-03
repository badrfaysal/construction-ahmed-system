<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Historical price tracking for common materials (متابعة أسعار الخامات).
// One row per material per time period — lets us see if prices are rising.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_price_history', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');       // e.g. "أسمنت (شيكارة)", "رمل (نقلة)"
            $table->string('period', 20);      // e.g. "01/25", "06/26" (month/year)
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // No two records for the same item in the same period
            $table->unique(['item_name', 'period']);
            $table->index('item_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_price_history');
    }
};
