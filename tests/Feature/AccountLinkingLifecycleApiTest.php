<?php

namespace Tests\Feature;

use App\Accounts\AccountResolver;
use App\Identity\VerifiedExternal;
use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountLinkingLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_requires_current_target_and_fresh_verified_assertion_and_replay_is_idempotent(): void
    {
        $token = $this->serviceToken('logres');
        $account = Account::query()->create();
        $payload = $this->externalPayload('second-subject');
        $url = "/api/v1/accounts/{$account->id}/identities/link";

        $this->withToken($token)->postJson($url, $payload)->assertForbidden();
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', 'acc_wrong')
            ->postJson($url, $payload)->assertForbidden();

        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->id)
            ->postJson($url, $payload)->assertOk()->assertJsonPath('outcome', 'linked');
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->id)
            ->postJson($url, $payload)->assertOk()->assertJsonPath('outcome', 'linked');
        $this->assertDatabaseCount('external_identities', 1);
        $this->assertDatabaseHas('account_resolution_events', ['outcome' => 'link_replayed', 'caller' => 'logres']);

        $stale = $this->externalPayload('stale-subject', CarbonImmutable::now()->subHour());
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->id)
            ->postJson($url, $stale)->assertConflict()->assertExactJson(['message' => 'Identity linking failed.']);
    }

    public function test_cross_account_link_collision_exposes_no_owner(): void
    {
        $token = $this->serviceToken('logres');
        $resolver = app(AccountResolver::class);
        $owner = $resolver->resolve($this->verified('owned-subject'));
        $target = Account::query()->create();

        $response = $this->withToken($token)->withHeader('X-Zahir-Current-Account', $target->id)
            ->postJson("/api/v1/accounts/{$target->id}/identities/link", $this->externalPayload('owned-subject'));

        $response->assertConflict()->assertExactJson(['message' => 'Identity linking failed.']);
        self::assertStringNotContainsString($owner->accountId, $response->getContent());
    }

    public function test_unlink_is_idempotent_and_last_identity_requires_privileged_recovery_reference(): void
    {
        $token = $this->serviceToken('logres');
        $adminToken = $this->serviceToken('account-admin', true);
        $resolver = app(AccountResolver::class);
        $account = $resolver->resolve($this->verified('primary'));
        $resolver->link($account->accountId, $this->verified('secondary'));
        $url = "/api/v1/accounts/{$account->accountId}/identities";

        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->accountId)
            ->deleteJson($url, ['provider' => 'workos', 'provider_subject' => 'secondary'])
            ->assertOk()->assertJsonPath('outcome', 'unlinked');
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->accountId)
            ->deleteJson($url, ['provider' => 'workos', 'provider_subject' => 'secondary'])
            ->assertOk()->assertJsonPath('outcome', 'unchanged');
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->accountId)
            ->deleteJson($url, ['provider' => 'workos', 'provider_subject' => 'primary'])
            ->assertConflict();
        $this->withToken($token)->withHeader('X-Zahir-Current-Account', $account->accountId)
            ->deleteJson($url, [
                'provider' => 'workos',
                'provider_subject' => 'primary',
                'accepted_recovery_reference' => 'recovery-case-123',
            ])->assertForbidden();
        $this->withToken($adminToken)->withHeader('X-Zahir-Current-Account', $account->accountId)
            ->deleteJson($url, [
                'provider' => 'workos',
                'provider_subject' => 'primary',
                'accepted_recovery_reference' => 'recovery-case-123',
            ])->assertOk()->assertJsonPath('outcome', 'unlinked');
    }

    public function test_lifecycle_requires_explicit_authority_and_is_separately_audited(): void
    {
        $productToken = $this->serviceToken('logres');
        $adminToken = $this->serviceToken('account-admin', true);
        $account = Account::query()->create();
        $url = "/api/v1/accounts/{$account->id}/suspension";

        $this->withToken($productToken)->postJson($url, ['reason' => 'risk review'])->assertForbidden();
        $this->withToken($adminToken)->postJson($url, ['reason' => 'risk review'])
            ->assertOk()->assertJsonPath('account.status', 'suspended');
        $this->withToken($adminToken)->deleteJson($url, ['reason' => 'review completed'])
            ->assertOk()->assertJsonPath('account.status', 'active');

        $this->assertDatabaseCount('account_lifecycle_events', 2);
        $this->assertDatabaseHas('account_lifecycle_events', [
            'account_id' => $account->id,
            'from_status' => 'active',
            'to_status' => 'suspended',
            'caller' => 'account-admin',
        ]);
    }

    private function externalPayload(string $subject, ?CarbonImmutable $authenticatedAt = null): array
    {
        $authenticatedAt ??= CarbonImmutable::now();

        return ['external' => [
            'provider' => 'workos',
            'provider_subject' => $subject,
            'claims' => [],
            'provenance' => $this->provenance(),
            'authenticated_at' => $authenticatedAt->toIso8601ZuluString(),
        ]];
    }

    private function verified(string $subject): VerifiedExternal
    {
        return new VerifiedExternal('workos', $subject, [], $this->provenance(), CarbonImmutable::now());
    }

    private function provenance(): array
    {
        return [
            'issuer' => 'https://api.workos.com/',
            'audience' => 'client_123',
            'asserted_at' => CarbonImmutable::now()->toIso8601ZuluString(),
        ];
    }
}
