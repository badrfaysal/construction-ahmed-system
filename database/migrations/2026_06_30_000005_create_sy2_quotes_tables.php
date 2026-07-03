<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Price quotes sent to potential clients (عروض الأسعار).
// A quote becomes a project when the client approves and signs.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 50)->unique();   // e.g. "QT-2026-001"
            $table->string('client_name');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->decimal('area', 8, 2)->nullable();   // apartment size in m²
            $table->date('date');
            // draft = still editing, sent = sent to client, approved = client said yes
            $table->enum('status', ['draft', 'sent', 'approved'])->default('draft');
            $table->text('note')->nullable();
            // if approved, optionally link to the project it created
            $table->foreignId('project_id')->nullable()->constrained('sy2_projects')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('date');
        });

        // Line items inside a quote — the breakdown by work phase
        Schema::create('sy2_quote_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('sy2_quotes')->cascadeOnDelete();
            $table->string('name');           // e.g. "محارة", "سيراميك وأرضيات"
            $table->decimal('price', 12, 2);
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_quote_bands');
        Schema::dropIfExists('sy2_quotes');
    }
};
