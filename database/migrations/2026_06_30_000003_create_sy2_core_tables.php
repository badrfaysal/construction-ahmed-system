<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Core business tables for the construction management system.
// Order matters: referenced tables must be created before the ones that FK into them.
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------
        // sy2_clients — the people who hire us for construction work
        // ------------------------------------------------------------------
        Schema::create('sy2_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Fast lookup by name when searching or auto-completing
            $table->index('name');
        });

        // ------------------------------------------------------------------
        // sy2_suppliers — companies or people we buy materials from
        // ------------------------------------------------------------------
        Schema::create('sy2_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        // ------------------------------------------------------------------
        // sy2_projects — each apartment / renovation job we take on
        // ------------------------------------------------------------------
        Schema::create('sy2_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('sy2_clients')->cascadeOnDelete();
            $table->string('name');           // e.g. "شقة الزمالك"
            $table->text('address')->nullable();
            $table->decimal('area', 8, 2)->nullable();   // square meters
            $table->date('start_date')->nullable();
            $table->date('deliver_date')->nullable();     // planned delivery
            $table->date('delivered_date')->nullable();   // actual delivery (when done=true)
            $table->tinyInteger('current_stage')->unsigned()->default(0); // progress 0-8
            // active = work in progress, done = delivered to client
            $table->enum('status', ['active', 'done'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');          // common filter: show active / done
            $table->index('start_date');
            $table->index('client_id');
        });

        // ------------------------------------------------------------------
        // sy2_project_bands — work phases (بنود) within a project.
        // Each band has an agreed client price, a labor cost, and materials.
        // ------------------------------------------------------------------
        Schema::create('sy2_project_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->string('name');                   // e.g. "محارة", "سيراميك وأرضيات"
            $table->decimal('client_price', 12, 2)->default(0); // what client pays for this band
            $table->enum('status', ['pending', 'active', 'done'])->default('pending');
            $table->string('contract_type', 100)->nullable(); // "بالمتر" / "مقاولة مقطوعة"
            $table->string('team_name')->nullable();          // worker or team assigned
            $table->decimal('labor_amount', 12, 2)->default(0); // amount paid to workers
            $table->date('labor_date')->nullable();           // date labor was paid/started
            $table->tinyInteger('sort_order')->unsigned()->default(0); // display order
            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
        });

        // ------------------------------------------------------------------
        // sy2_materials — everything purchased for a project band
        // ------------------------------------------------------------------
        Schema::create('sy2_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->foreignId('band_id')->nullable()->constrained('sy2_project_bands')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('sy2_suppliers')->nullOnDelete();
            $table->string('item');          // material name, e.g. "أسمنت"
            $table->string('unit', 50);      // e.g. "شيكارة", "م²", "نقلة"
            $table->decimal('qty', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('returned_qty', 10, 2)->default(0); // المرتجع
            $table->date('date');
            $table->timestamps();

            $table->index('project_id');
            $table->index('band_id');
            $table->index('supplier_id');
            $table->index('date');
            $table->index('item');           // used in supplier price comparison queries
        });

        // ------------------------------------------------------------------
        // sy2_installments — payment plan for each project (أقساط العميل)
        // ------------------------------------------------------------------
        Schema::create('sy2_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();
            $table->string('label');          // e.g. "دفعة مقدم (25%)", "القسط الأول"
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            // paid = collected, due = overdue/due now, upcoming = future installment
            $table->enum('status', ['paid', 'due', 'upcoming'])->default('upcoming');
            $table->string('payment_method', 100)->nullable(); // "كاش", "تحويل بنكي"
            $table->date('paid_date')->nullable();             // actual date money was received
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            $table->index('due_date');        // used to flag overdue installments
        });
    }

    public function down(): void
    {
        // Drop in reverse order (child tables before parents)
        Schema::dropIfExists('sy2_installments');
        Schema::dropIfExists('sy2_materials');
        Schema::dropIfExists('sy2_project_bands');
        Schema::dropIfExists('sy2_projects');
        Schema::dropIfExists('sy2_suppliers');
        Schema::dropIfExists('sy2_clients');
    }
};
