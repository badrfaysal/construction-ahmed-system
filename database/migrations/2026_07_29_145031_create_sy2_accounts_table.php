<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sy2_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Create the default wallet for Construction (المقاولات)
        DB::table('sy2_accounts')->insert([
            'id' => 37, // Keep ID 37 to match existing foreign keys seamlessly if possible, though not strictly required if we update defaults
            'name' => 'المقاولات',
            'category' => 'project_sector',
            'initial_balance' => 0,
            'balance' => 0, // We will calculate this based on existing transactions later if needed, or leave at 0.
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_accounts');
    }
};
