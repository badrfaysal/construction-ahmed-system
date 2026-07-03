<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Replaces the old free-text contract_type with a proper coded enum, plus the
// qty/rate pair needed to compute labor_amount for daily/per-meter contracts.
// Best-effort maps any existing free-text values to the new enum so real
// bands already entered (e.g. "يوميه") don't lose their contract type.
return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('sy2_project_bands')->pluck('contract_type', 'id');

        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->dropColumn('contract_type');
        });

        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->enum('contract_type', ['lump_sum', 'daily', 'per_meter'])->nullable()->after('status');
            $table->decimal('contract_qty', 10, 2)->nullable()->after('contract_type');
            $table->decimal('contract_unit_rate', 12, 2)->nullable()->after('contract_qty');
        });

        foreach ($existing as $id => $value) {
            $mapped = match (true) {
                str_contains((string) $value, 'يوم') => 'daily',
                str_contains((string) $value, 'متر') => 'per_meter',
                ! empty($value) => 'lump_sum',
                default => null,
            };

            if ($mapped) {
                DB::table('sy2_project_bands')->where('id', $id)->update(['contract_type' => $mapped]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'contract_qty', 'contract_unit_rate']);
        });

        Schema::table('sy2_project_bands', function (Blueprint $table) {
            $table->string('contract_type', 100)->nullable()->after('status');
        });
    }
};
