<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Distinguishes normal material purchases from miscellaneous expenses
// (tips/transport/meals) that aren't tied to a specific item but are still
// billed to the client exactly like a material.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->string('category', 30)->default('material')->after('band_id');
        });
    }

    public function down(): void
    {
        Schema::table('sy2_materials', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
