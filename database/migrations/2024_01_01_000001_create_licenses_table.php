<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('license_key', 19)->unique(); // XXXX-XXXX-XXXX-XXXX
            $table->string('client_name', 150);
            $table->string('client_email', 150);
            $table->string('domain', 253);              // Bound domain
            $table->string('product_name', 100)->default('default');
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'revoked'])->default('pending');
            $table->unsignedTinyInteger('grace_period_days')->default(3);
            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable();
            $table->json('features')->nullable();        // Feature flags
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['license_key', 'status']);
            $table->index(['expires_at', 'status']);
        });

        Schema::create('license_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('license_id');
            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
            $table->enum('event_type', [
                'issued', 'activated', 'renewed', 'revoked',
                'suspended', 'token_generated', 'expiry_warning'
            ]);
            $table->string('ip_address', 45)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at');

            $table->index(['license_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_events');
        Schema::dropIfExists('licenses');
    }
};
