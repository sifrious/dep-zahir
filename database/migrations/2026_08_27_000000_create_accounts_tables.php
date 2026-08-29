<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->string('id', 30)->primary();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('external_identities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 64);
            $table->string('provider_subject', 512);
            $table->json('verified_claims');
            $table->json('provenance');
            $table->timestamp('linked_at');
            $table->timestamp('last_authenticated_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_subject']);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('entitlement_grants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('entitlement');
            $table->string('source');
            $table->string('source_reference')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'product_id', 'entitlement']);
        });

        Schema::create('account_resolution_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('account_id', 30)->nullable()->index();
            $table->string('provider', 64);
            $table->string('provider_subject_hash', 64);
            $table->string('outcome', 64);
            $table->string('caller', 128)->nullable();
            $table->json('provenance');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_resolution_events');
        Schema::dropIfExists('entitlement_grants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('external_identities');
        Schema::dropIfExists('accounts');
    }
};
