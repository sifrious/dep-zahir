<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_callers', function (Blueprint $table): void {
            $table->boolean('can_manage_account_lifecycle')->default(false);
        });

        Schema::create('account_lifecycle_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('account_id', 30)->index();
            $table->string('from_status');
            $table->string('to_status');
            $table->string('caller', 128);
            $table->string('reason', 512);
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_lifecycle_events');
        Schema::table('service_callers', function (Blueprint $table): void {
            $table->dropColumn('can_manage_account_lifecycle');
        });
    }
};
