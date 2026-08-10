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
        Schema::create('sy2_asset_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->string('action'); // purchase, depreciation, edit, sale
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('sy2_assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy2_asset_logs');
    }
};
