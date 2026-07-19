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
        DB::statement("ALTER TABLE sy2_projects MODIFY COLUMN status ENUM('active', 'done', 'suspended', 'canceled') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE sy2_projects MODIFY COLUMN status ENUM('active', 'done') NOT NULL DEFAULT 'active'");
    }
};
