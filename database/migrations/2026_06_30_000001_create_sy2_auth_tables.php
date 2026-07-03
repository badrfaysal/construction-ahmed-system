<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates authentication tables for the construction management system.
// All tables are prefixed with sy2_ because this database is shared across
// multiple projects, and the prefix is how we tell them apart.
return new class extends Migration
{
    public function up(): void
    {
        // Main users table — stores owner and staff accounts for this app only.
        // Does NOT share or reuse the legacy "users" table that belongs to another project.
        Schema::create('sy2_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // owner = full access, staff = limited (read-only on financial data)
            $table->enum('role', ['owner', 'staff'])->default('staff');
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });

        // Temporary tokens for the "forgot password" flow.
        Schema::create('sy2_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Database-backed sessions (SESSION_TABLE=sy2_sessions in .env).
        Schema::create('sy2_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_sessions');
        Schema::dropIfExists('sy2_password_reset_tokens');
        Schema::dropIfExists('sy2_users');
    }
};
