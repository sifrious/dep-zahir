<?php

namespace Tests\Feature;

use App\Models\ServiceCaller;
use App\Models\ServiceCredential;
use App\Services\ServiceCredentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class ServiceAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_credentials_overlap_rotate_revoke_and_never_store_raw_tokens(): void
    {
        $caller = ServiceCaller::query()->create(['key' => 'logres', 'name' => 'Logres']);
        $credentials = app(ServiceCredentials::class);
        $old = $credentials->issue($caller, 'old');
        $new = $credentials->issue($caller, 'new');

        $this->withToken($old)->postJson('/api/v1/accounts/resolve', [])->assertUnprocessable();
        $this->withToken($new)->postJson('/api/v1/accounts/resolve', [])->assertUnprocessable();
        self::assertFalse(ServiceCredential::query()->where('secret_hash', $old)->exists());
        self::assertStringNotContainsString(substr($old, -64), (string) ServiceCredential::query()->first()->secret_hash);

        $oldId = explode('.', $old)[1];
        ServiceCredential::query()->findOrFail($oldId)->update(['revoked_at' => now()]);
        $this->withToken($old)->postJson('/api/v1/accounts/resolve', [])->assertUnauthorized();
        $this->withToken($new)->postJson('/api/v1/accounts/resolve', [])->assertUnprocessable();

        $this->assertDatabaseHas('service_request_events', [
            'caller_key' => 'logres',
            'service_credential_id' => explode('.', $new)[1],
            'response_status' => 422,
        ]);
    }

    public function test_malformed_wrong_expired_revoked_and_disabled_credentials_fail_closed(): void
    {
        $caller = ServiceCaller::query()->create(['key' => 'logres', 'name' => 'Logres']);
        $credentials = app(ServiceCredentials::class);
        $valid = $credentials->issue($caller, 'valid');

        $wrong = substr($valid, 0, -64).str_repeat('x', 64);
        $this->withToken('malformed')->postJson('/api/v1/accounts/resolve')->assertUnauthorized();
        $this->withToken($wrong)->postJson('/api/v1/accounts/resolve')->assertUnauthorized();

        $expired = $credentials->issue($caller, 'expired', now()->subMinute());
        $this->withToken($expired)->postJson('/api/v1/accounts/resolve')->assertUnauthorized();

        $caller->update(['disabled_at' => now()]);
        $this->withToken($valid)->postJson('/api/v1/accounts/resolve')->assertUnauthorized();
    }

    public function test_request_size_and_rate_limits_protect_service_endpoints(): void
    {
        config(['zahir.maximum_request_bytes' => 10]);
        $token = $this->serviceToken('limits');
        $this->withToken($token)->postJson('/api/v1/accounts/resolve', ['long' => str_repeat('x', 20)])
            ->assertStatus(413);

        config(['zahir.maximum_request_bytes' => 32768, 'zahir.requests_per_minute' => 1]);
        $rateToken = app(ServiceCredentials::class)->issue(ServiceCaller::query()->where('key', 'limits')->firstOrFail(), 'rate');
        RateLimiter::clear(hash('sha256', $rateToken));
        $this->withToken($rateToken)->postJson('/api/v1/accounts/resolve', [])->assertUnprocessable();
        $this->withToken($rateToken)->postJson('/api/v1/accounts/resolve', [])->assertTooManyRequests();
    }
}
