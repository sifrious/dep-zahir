<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExternalIdentityLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_revocation_and_recovery_are_authorized_audited_and_retry_safe(): void
    {
        $productToken = $this->serviceToken('logres');
        $lifecycleToken = $this->serviceToken('account-admin', true);
        $external = $this->externalPayload('fixture-subject');

        $accountId = $this->withToken($productToken)
            ->postJson('/api/v1/accounts/resolve', ['external' => $external])
            ->assertOk()
            ->assertJsonPath('account.authentication_outcome', 'authenticated')
            ->json('account.id');
        $url = "/api/v1/accounts/{$accountId}/identities/revocation";
        $revocation = [
            'provider' => 'fixture-provider',
            'provider_subject' => 'fixture-subject',
            'reason_code' => 'provider_revoked',
        ];

        $this->withToken($productToken)->postJson($url, $revocation)->assertForbidden();
        $this->withToken($lifecycleToken)->postJson($url, $revocation)
            ->assertOk()
            ->assertExactJson([
                'account_id' => $accountId,
                'identity_status' => 'revoked',
                'result' => 'revoked',
                'authentication_outcome' => 'provider_failed',
                'authentication_reason' => 'provider_revoked',
                'replayed' => false,
            ]);
        $this->withToken($lifecycleToken)->postJson($url, $revocation)
            ->assertOk()
            ->assertJsonPath('authentication_outcome', 'provider_failed')
            ->assertJsonPath('replayed', true);

        $this->assertDatabaseHas('external_identities', [
            'account_id' => $accountId,
            'provider' => 'fixture-provider',
            'provider_subject' => 'fixture-subject',
            'status' => 'revoked',
        ]);
        $this->assertDatabaseHas('external_identity_lifecycle_events', [
            'account_id' => $accountId,
            'provider' => 'fixture-provider',
            'provider_subject_hash' => hash('sha256', 'fixture-subject'),
            'from_status' => 'active',
            'to_status' => 'revoked',
            'caller' => 'account-admin',
            'reason_code' => 'provider_revoked',
        ]);

        $this->withToken($productToken)
            ->postJson('/api/v1/accounts/resolve', ['external' => $external])
            ->assertOk()
            ->assertJsonPath('account.id', $accountId)
            ->assertJsonPath('account.created', false)
            ->assertJsonPath('account.authentication_outcome', 'provider_failed')
            ->assertJsonPath('account.authentication_reason', 'provider_revoked');
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('external_identities', 1);

        $this->withToken($lifecycleToken)->deleteJson($url, $revocation)->assertUnprocessable();
        $recovery = [
            'provider' => 'fixture-provider',
            'provider_subject' => 'fixture-subject',
            'reason_code' => 'provider_reverified',
            'accepted_recovery_reference' => 'fixture-recovery-reference',
        ];
        $this->withToken($lifecycleToken)->deleteJson($url, $recovery)
            ->assertOk()
            ->assertExactJson([
                'account_id' => $accountId,
                'identity_status' => 'active',
                'result' => 'recovered',
                'replayed' => false,
            ]);
        $this->withToken($lifecycleToken)->deleteJson($url, $recovery)
            ->assertOk()
            ->assertJsonPath('replayed', true);

        $this->assertDatabaseHas('external_identity_lifecycle_events', [
            'account_id' => $accountId,
            'from_status' => 'revoked',
            'to_status' => 'active',
            'reason_code' => 'provider_reverified',
            'recovery_reference_hash' => hash('sha256', 'fixture-recovery-reference'),
        ]);
        $this->assertDatabaseMissing('external_identity_lifecycle_events', [
            'provider_subject_hash' => 'fixture-subject',
        ]);
        $this->assertDatabaseMissing('external_identity_lifecycle_events', [
            'recovery_reference_hash' => 'fixture-recovery-reference',
        ]);

        $this->withToken($productToken)
            ->postJson('/api/v1/accounts/resolve', ['external' => $external])
            ->assertOk()
            ->assertJsonPath('account.id', $accountId)
            ->assertJsonPath('account.authentication_outcome', 'authenticated')
            ->assertJsonPath('account.authentication_reason', null);
        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_suspended_account_resolves_without_becoming_authenticated(): void
    {
        $productToken = $this->serviceToken('logres');
        $lifecycleToken = $this->serviceToken('account-admin', true);
        $external = $this->externalPayload('suspended-subject');
        $accountId = $this->withToken($productToken)
            ->postJson('/api/v1/accounts/resolve', ['external' => $external])
            ->json('account.id');

        $this->withToken($lifecycleToken)
            ->postJson("/api/v1/accounts/{$accountId}/suspension", ['reason' => 'risk_review'])
            ->assertOk();

        $this->withToken($productToken)
            ->postJson('/api/v1/accounts/resolve', ['external' => $external])
            ->assertOk()
            ->assertJsonPath('account.id', $accountId)
            ->assertJsonPath('account.status', 'suspended')
            ->assertJsonPath('account.authentication_outcome', 'suspended');
        $this->assertSame('suspended', Account::query()->findOrFail($accountId)->status->value);
    }

    /** @return array<string, mixed> */
    private function externalPayload(string $subject): array
    {
        return [
            'provider' => 'fixture-provider',
            'provider_subject' => $subject,
            'claims' => [],
            'provenance' => [
                'issuer' => 'urn:fixture:issuer',
                'audience' => 'fixture-product',
                'asserted_at' => '2026-09-04T12:00:00Z',
                'assertion_id' => "fixture-{$subject}",
            ],
            'authenticated_at' => '2026-09-04T12:00:00Z',
        ];
    }
}
