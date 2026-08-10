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
        Schema::create('sy2_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'sold', 'fully_depreciated'])->default('active')->index();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_assets');
    }
};
