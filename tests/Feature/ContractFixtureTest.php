<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ContractFixtureTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->serviceToken('fixture-client');
        Date::setTestNow('2026-08-29T12:00:00Z');
    }

    public function test_resolution_contract_matches_v1_fixture(): void
    {
        $case = $this->fixtures()['cases']['account.resolve.success'];
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/accounts/resolve', $case['request'])
            ->assertStatus($case['response']['status'])
            ->assertJsonPath('account.status', $case['response']['body']['account']['status'])
            ->assertJsonPath('account.created', $case['response']['body']['account']['created']);

        $this->assertStringStartsWith('acc_', $response->json('account.id'));
    }

    public function test_entitlement_contracts_match_v1_fixtures(): void
    {
        $product = Product::query()->create(['key' => 'logres', 'name' => 'Logres']);
        $active = Account::query()->create();
        EntitlementGrant::query()->create([
            'account_id' => $active->id, 'product_id' => $product->id,
            'entitlement' => 'access', 'source' => 'fixture',
        ]);

        $allow = $this->fixtures()['cases']['entitlement.allow'];
        $allow['request']['account_id'] = $active->id;
        $this->withToken($this->token)->postJson('/api/v1/entitlements/decide', $allow['request'])
            ->assertStatus($allow['response']['status'])
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('account_status', 'active');

        $active->update(['status' => 'suspended']);
        $deny = $this->fixtures()['cases']['entitlement.deny'];
        $deny['request']['account_id'] = $active->id;
        $this->withToken($this->token)->postJson('/api/v1/entitlements/decide', $deny['request'])
            ->assertStatus($deny['response']['status'])
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('account_status', 'suspended')
            ->assertJsonPath('grant_id', null);
    }

    public function test_error_contracts_match_v1_fixtures(): void
    {
        $fixtures = $this->fixtures()['cases'];
        $this->postJson('/api/v1/accounts/resolve')
            ->assertStatus($fixtures['error.authentication']['response']['status'])
            ->assertExactJson($fixtures['error.authentication']['response']['body']);

        $response = $this->withToken($this->token)->postJson('/api/v1/accounts/resolve', ['external' => []])
            ->assertStatus($fixtures['error.validation']['response']['status']);
        $this->assertSame(
            $fixtures['error.validation']['response']['body']['errors']['external.provider'][0],
            $response->json('errors')['external.provider'][0],
        );

        $this->withToken($this->token)->postJson('/api/v1/entitlements/decide', [
            'account_id' => 'acc_00000000000000000000000000',
            'product' => 'logres',
            'entitlement' => 'access',
        ])->assertStatus($fixtures['error.not_found']['response']['status']);
    }

    public function test_recovery_contract_matches_v1_fixture(): void
    {
        $case = $this->fixtures()['cases']['error.recovery_required'];
        $account = Account::query()->create();
        $account->externalIdentities()->create([
            'provider' => 'workos',
            'provider_subject' => 'user_sole_identity',
            'verified_claims' => [],
            'provenance' => [],
            'linked_at' => now(),
        ]);

        $this->withToken($this->token)
            ->withHeader('X-Zahir-Current-Account', $account->id)
            ->deleteJson("/api/v1/accounts/{$account->id}/identities", [
                'provider' => 'workos',
                'provider_subject' => 'user_sole_identity',
            ])
            ->assertStatus($case['response']['status'])
            ->assertExactJson($case['response']['body']);
    }

    /**
     * A fixture nobody asserts is documentation pretending to be a test. Freezing
     * the case list means adding one is a deliberate edit here, which is the
     * moment to also write the assertion that exercises it.
     */
    public function test_the_declared_fixture_case_list_is_frozen(): void
    {
        $cases = array_keys($this->fixtures()['cases']);
        sort($cases);

        $this->assertSame([
            'account.resolve.success',
            'entitlement.allow',
            'entitlement.deny',
            'error.authentication',
            // Defence in depth: the unique index on (provider, provider_subject)
            // makes an ambiguous resolve unreachable over HTTP, so this shape is
            // asserted at the domain layer by AccountResolverTest instead.
            'error.collision',
            'error.not_found',
            'error.recovery_required',
            'error.validation',
        ], $cases);
    }

    /** @return array<string, mixed> */
    private function fixtures(): array
    {
        return json_decode(
            file_get_contents(base_path('contracts/v1/fixtures.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
