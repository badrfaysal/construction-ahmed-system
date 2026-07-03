<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates Laravel's cache and queue infrastructure tables under the sy2_ prefix
// so they don't collide with the same-named tables used by other projects in this DB.
return new class extends Migration
{
    public function up(): void
    {
        // Cache store — used when CACHE_STORE=database (see .env DB_CACHE_TABLE).
        Schema::create('sy2_cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        // Distributed cache locking (prevents race conditions on cached data).
        Schema::create('sy2_cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        // Background job queue (QUEUE_CONNECTION=database, DB_QUEUE_TABLE=sy2_jobs).
        Schema::create('sy2_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Batched jobs — groups of background jobs run together.
        Schema::create('sy2_job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Failed jobs — stores jobs that threw exceptions for later inspection.
        Schema::create('sy2_failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_failed_jobs');
        Schema::dropIfExists('sy2_job_batches');
        Schema::dropIfExists('sy2_jobs');
        Schema::dropIfExists('sy2_cache_locks');
        Schema::dropIfExists('sy2_cache');
    }
};
