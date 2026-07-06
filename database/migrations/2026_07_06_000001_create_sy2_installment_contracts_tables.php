<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// نظام العقود والأقساط (منقول بروح السيستم الأول، بس العقد هنا مربوط بمشروع):
//  - sy2_installment_contracts : كل صف = عقد تقسيط لمشروع/عميل (إجمالي + مقدم + خطة شهرية)
//  - sy2_installment_payments  : الدفعات المتعددة اللي بتتحصّل على العقد بمرور الوقت
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_installment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('sy2_projects')->cascadeOnDelete();

            // لقطة من بيانات العميل وقت الإنشاء — للعرض/البحث/الواتساب من غير join كل مرة
            $table->string('customer_name');
            $table->string('customer_phone', 30)->nullable();
            $table->string('product_name')->nullable(); // اسم المشروع/البند المتعاقد عليه

            // القيم المالية (نفس منطق حساب السيستم الأول)
            $table->decimal('cash_price', 15, 2)->default(0);          // السعر كاش (فاتورة العميل وقت الإنشاء)
            $table->decimal('discount', 15, 2)->default(0);            // خصم إن وجد
            $table->decimal('down_payment', 15, 2)->default(0);        // المقدم المدفوع وقت الإنشاء
            $table->decimal('interest_rate', 6, 2)->default(0);        // نسبة الفائدة %
            $table->unsignedInteger('installment_months')->default(0); // عدد الشهور
            $table->decimal('total_after_interest', 15, 2)->default(0);// إجمالي العقد بعد الفائدة (شامل المقدم)
            $table->decimal('monthly_installment', 15, 2)->default(0); // القسط الشهري الثابت
            $table->unsignedTinyInteger('due_day')->default(1);        // يوم السداد الشهري (1-31)
            $table->decimal('remaining_balance', 15, 2)->default(0);   // المتبقي بالخارج (= total - down - دفعات)

            $table->date('start_date')->nullable();
            $table->string('status', 32)->default('active');           // active / ... (لتوسّع لاحق)
            $table->string('close_reason', 32)->nullable();            // written_off / terminated (لاحقًا)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'remaining_balance']);
            $table->index('due_day');
            $table->index('customer_phone');
        });

        Schema::create('sy2_installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('sy2_installment_contracts')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('sy2_projects')->nullOnDelete();
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('discount_applied', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('method', 50)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('contract_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_installment_payments');
        Schema::dropIfExists('sy2_installment_contracts');
    }
};
