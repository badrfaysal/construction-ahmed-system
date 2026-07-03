<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Replaces the single returned_qty snapshot on sy2_materials with a proper
// append-only ledger — a purchase can have returns added at any later date,
// not just recorded once at purchase time. Any existing returned_qty values
// are migrated into a matching return row before the old column is dropped.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_material_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('sy2_materials')->cascadeOnDelete();
            $table->decimal('qty', 10, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('material_id');
        });

        $existing = DB::table('sy2_materials')->where('returned_qty', '>', 0)->get(['id', 'returned_qty', 'date']);
        foreach ($existing as $m) {
            DB::table('sy2_material_returns')->insert([
                'material_id' => $m->id,
                'qty'         => $m->returned_qty,
                'date'        => $m->date,
                'notes'       => 'منقول من التسجيل الأصلي',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn('returned_qty');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->decimal('returned_qty', 10, 2)->default(0)->after('supervision_pct');
        });

        foreach (DB::table('sy2_material_returns')->get() as $r) {
            DB::table('sy2_materials')->where('id', $r->material_id)
                ->increment('returned_qty', $r->qty);
        }

        Schema::dropIfExists('sy2_material_returns');
    }
};
