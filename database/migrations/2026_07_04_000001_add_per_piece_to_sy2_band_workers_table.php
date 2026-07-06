<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Some work is priced per piece, not per meter (e.g. doors & windows inside a
// "دهانات" band) — add 'per_piece' as a contract type alongside per_meter.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sy2_band_workers MODIFY contract_type ENUM('lump_sum','daily','per_meter','per_piece') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sy2_band_workers MODIFY contract_type ENUM('lump_sum','daily','per_meter') NULL");
    }
};
