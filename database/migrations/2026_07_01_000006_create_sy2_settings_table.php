<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Single-row table holding system-wide configurable values (default supervision
// percentage, company info shown on printed documents, WhatsApp country code).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sy2_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_supervision_pct', 5, 2)->default(0);
            $table->string('company_name')->default('شركة الضبع للتجارة والتوريدات');
            $table->string('company_tagline')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_registration')->nullable();
            $table->string('whatsapp_country_code', 5)->default('20');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sy2_settings');
    }
};
