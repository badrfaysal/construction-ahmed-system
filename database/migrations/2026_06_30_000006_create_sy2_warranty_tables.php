<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Warranty tracking for completed projects (متابعة الضمانات).
// One warranty record per completed project, with a list of reported complaints.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_warranties', function (Blueprint $table) {
            $table->id();
            // One warranty per completed project
            $table->foreignId('project_id')->unique()->constrained('sy2_projects')->cascadeOnDelete();
            $table->date('start_date');          // usually the delivery date
            $table->tinyInteger('months')->unsigned()->default(12);
            $table->timestamps();
        });

        // Client complaints reported during the warranty period
        Schema::create('sy2_warranty_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained('sy2_warranties')->cascadeOnDelete();
            $table->date('date');
            $table->text('description');
            // Free-form status text: "تم الإصلاح", "قيد الإصلاح", "مرفوض" etc.
            $table->string('status', 100)->default('pending');
            $table->timestamps();

            $table->index('warranty_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_warranty_complaints');
        Schema::dropIfExists('sy2_warranties');
    }
};
