<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_callers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('key', 128)->unique();
            $table->string('name');
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_credentials', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('service_caller_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('secret_hash');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['service_caller_id', 'revoked_at']);
        });

        Schema::create('service_request_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('service_caller_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('service_credential_id')->nullable()->constrained()->nullOnDelete();
            $table->string('caller_key', 128)->nullable();
            $table->string('method', 12);
            $table->string('route', 255);
            $table->unsignedSmallInteger('response_status');
            $table->string('request_id', 64);
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_events');
        Schema::dropIfExists('service_credentials');
        Schema::dropIfExists('service_callers');
    }
};
