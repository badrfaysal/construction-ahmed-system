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
        Schema::create('sy2_calculators', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('work_type')->default('both');
            $table->decimal('global_height', 8, 2)->default(2.8);
            $table->json('spaces')->nullable();
            $table->decimal('total_paints', 10, 2)->default(0);
            $table->decimal('total_floor_ceramics', 10, 2)->default(0);
            $table->decimal('total_wall_ceramics', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_calculators');
    }
};
