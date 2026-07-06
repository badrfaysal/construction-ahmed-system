<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// سجل تدقيق ثابت (append-only) لكل حركة مالية — بيتكتب لما تُنشأ/تتعدّل/تتحذف
// أي حركة sy2_transactions، وهو نفسه لا يتعدّل ولا يتحذف أبداً. ده اللي بيخلي
// "سجل الحركات" يعرض كل حاجة حصلت فعلاً (حتى لو اتلغت أو اتعدّلت بعد كده)
// بدل ما يعرض بس الحالة الحيّة النهائية.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 20); // created | updated | deleted
            $table->unsignedBigInteger('transaction_id')->nullable(); // بيفضل موجود حتى بعد حذف الحركة الأصلية
            $table->string('direction', 10)->nullable(); // in | out
            $table->string('type')->nullable();
            $table->string('party')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('band_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('ref_type', 50)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('description', 1000)->nullable();
            $table->date('date')->nullable();
            $table->json('old_values')->nullable(); // للـ updated بس — القيم قبل التعديل
            $table->unsignedBigInteger('performed_by')->nullable(); // sy2_users... لأ، users (المستخدم الموحّد)
            $table->timestamp('happened_at');

            $table->index(['transaction_id']);
            $table->index(['project_id']);
            $table->index(['action']);
            $table->index(['happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_audit_logs');
    }
};
