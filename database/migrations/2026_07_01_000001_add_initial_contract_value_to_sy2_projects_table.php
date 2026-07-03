<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Locks in the price agreed with the client at the moment an approved Quote
// becomes a Project, instead of always recomputing it live from band prices.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->decimal('initial_contract_value', 12, 2)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_projects', function (Blueprint $table) {
            $table->dropColumn('initial_contract_value');
        });
    }
};
