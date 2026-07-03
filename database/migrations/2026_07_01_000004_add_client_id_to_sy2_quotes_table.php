<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ties a quote to a real, already-registered client instead of freeform text —
// client_name/phone stay on the row (still used everywhere for display/print)
// but are now copied from the selected Client at save time, not typed by hand.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('ref')->constrained('sy2_clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sy2_quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
