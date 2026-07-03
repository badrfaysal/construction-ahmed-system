<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-project default supervision %, used to pre-fill the supervision field on
// every band/material/worker in that project (still editable everywhere).
// Falls back to the global Settings default when zero.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->decimal('default_supervision_pct', 5, 2)->default(0)->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->dropColumn('default_supervision_pct');
        });
    }
};
