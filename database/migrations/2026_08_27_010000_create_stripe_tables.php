<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_customers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('customer_id')->unique();
            $table->timestamps();
        });

        Schema::create('stripe_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('type');
            $table->boolean('livemode');
            $table->timestamp('occurred_at');
            $table->timestamp('processed_at');
            $table->string('payload_sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_events');
        Schema::dropIfExists('stripe_customers');
    }
};
