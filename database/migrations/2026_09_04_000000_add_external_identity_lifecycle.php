<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_identities', function (Blueprint $table): void {
            $table->string('status')->default('active')->after('provider_subject');
            $table->timestamp('revoked_at')->nullable()->after('last_authenticated_at');
            $table->timestamp('recovered_at')->nullable()->after('revoked_at');
        });

        Schema::create('external_identity_lifecycle_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('account_id', 30)->index();
            $table->string('provider', 64);
            $table->string('provider_subject_hash', 64);
            $table->string('from_status');
            $table->string('to_status');
            $table->string('caller', 128);
            $table->string('reason_code', 128);
            $table->string('recovery_reference_hash', 64)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_identity_lifecycle_events');
        Schema::table('external_identities', function (Blueprint $table): void {
            $table->dropColumn(['status', 'revoked_at', 'recovered_at']);
        });
    }
};
