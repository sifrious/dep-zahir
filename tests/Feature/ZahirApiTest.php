<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ZahirApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->serviceToken('logres');
    }

    public function test_contracts_require_authenticated_service_caller(): void
    {
        $this->postJson('/api/v1/accounts/resolve')->assertUnauthorized();
        $this->postJson('/api/v1/entitlements/decide')->assertUnauthorized();
    }

    public function test_verified_identity_resolves_through_public_contract(): void
    {
        Log::spy();
        $response = $this->withToken($this->token)->postJson('/api/v1/accounts/resolve', [
            'external' => [
                'provider' => 'workos', 'provider_subject' => 'user_123',
                'claims' => ['email' => 'person@example.test', 'email_verified' => true],
                'provenance' => ['issuer' => 'https://api.workos.com/', 'audience' => 'client_123', 'asserted_at' => '2026-08-29T12:00:00Z'],
                'authenticated_at' => '2026-08-29T12:00:00Z',
            ],
        ]);

        $response->assertOk()->assertJsonPath('account.status', 'active')->assertJsonPath('account.created', true);
        $this->assertStringStartsWith('acc_', $response->json('account.id'));
        $this->assertDatabaseHas('account_resolution_events', ['caller' => 'logres', 'outcome' => 'created']);
        Log::shouldHaveReceived('info')->with(
            'zahir.account_resolution',
            Mockery::on(function (array $context): bool {
                $encoded = json_encode($context, JSON_THROW_ON_ERROR);

                return $context['outcome'] === 'created'
                    && isset($context['latency_ms'], $context['metric_count'])
                    && ! str_contains($encoded, 'user_123')
                    && ! str_contains($encoded, 'person@example.test')
                    && ! str_contains($encoded, $this->token);
            }),
        )->once();
    }

    public function test_entitlement_contract_allows_then_denies_suspended_account(): void
    {
        $account = Account::query()->create();
        $product = Product::query()->create(['key' => 'logres', 'name' => 'Logres']);
        EntitlementGrant::query()->create([
            'account_id' => $account->id, 'product_id' => $product->id,
            'entitlement' => 'access', 'source' => 'manual',
        ]);
        $payload = ['account_id' => $account->id, 'product' => 'logres', 'entitlement' => 'access'];

        $this->withToken($this->token)->postJson('/api/v1/entitlements/decide', $payload)
            ->assertOk()->assertJsonPath('allowed', true)->assertJsonPath('account_status', 'active');
        $account->update(['status' => 'suspended']);
        $this->withToken($this->token)->postJson('/api/v1/entitlements/decide', $payload)
            ->assertOk()->assertJsonPath('allowed', false)->assertJsonPath('account_status', 'suspended');
    }
}
