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
        Schema::create('license_keypairs', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->unique(); // e.g. "v1", "v2"
            $table->longText('private_key');          // encrypted
            $table->longText('public_key');           // plain (safe)
            $table->longText('fingerprint_secret');   // encrypted
            $table->boolean('is_active')->default(false); // only one will be active
            $table->string('note')->nullable();       // "Generated 2025-01-01"
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_keypairs');
    }
};
