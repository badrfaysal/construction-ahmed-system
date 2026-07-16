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
        Schema::create('sy2_project_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_project_discounts');
    }
};
