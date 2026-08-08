<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('sy2_audit_logs', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sy2_audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('sy2_audit_logs', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
