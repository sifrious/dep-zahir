<?php

namespace Tests\Feature;

use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The account's contact address: metadata a product can ask for, chosen across
 * every linked identity rather than assumed from one assertion.
 */
final class AccountContactAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolution_returns_the_asserted_address(): void
    {
        $token = $this->serviceToken('logres');

        $this->withToken($token)
            ->postJson('/api/v1/accounts/resolve', $this->payload('user_one', 'one@example.test'))
            ->assertOk()
            ->assertJsonPath('account.contact_email', 'one@example.test');
    }

    /**
     * The precedence rule. With several linked identities the most recently
     * authenticated one wins — it is the one the person demonstrably still
     * controls, rather than whichever happened to be linked first.
     */
    public function test_the_most_recently_authenticated_identity_supplies_the_address(): void
    {
        $account = Account::query()->create();

        $account->externalIdentities()->create([
            'provider' => 'workos', 'provider_subject' => 'older',
            'verified_claims' => ['email' => 'older@example.test'], 'provenance' => [],
            'linked_at' => CarbonImmutable::parse('2026-01-01T00:00:00Z'),
            'last_authenticated_at' => CarbonImmutable::parse('2026-01-01T00:00:00Z'),
        ]);
        $account->externalIdentities()->create([
            'provider' => 'other-idp', 'provider_subject' => 'newer',
            'verified_claims' => ['email' => 'newer@example.test'], 'provenance' => [],
            'linked_at' => CarbonImmutable::parse('2026-02-01T00:00:00Z'),
            'last_authenticated_at' => CarbonImmutable::parse('2026-03-01T00:00:00Z'),
        ]);

        self::assertSame('newer@example.test', $account->contactEmail());
    }

    /** An identity never used to sign in loses to one that has been. */
    public function test_a_never_authenticated_identity_does_not_win(): void
    {
        $account = Account::query()->create();

        $account->externalIdentities()->create([
            'provider' => 'workos', 'provider_subject' => 'used',
            'verified_claims' => ['email' => 'used@example.test'], 'provenance' => [],
            'linked_at' => CarbonImmutable::parse('2026-01-01T00:00:00Z'),
            'last_authenticated_at' => CarbonImmutable::parse('2026-01-02T00:00:00Z'),
        ]);
        $account->externalIdentities()->create([
            'provider' => 'other-idp', 'provider_subject' => 'unused',
            'verified_claims' => ['email' => 'unused@example.test'], 'provenance' => [],
            'linked_at' => CarbonImmutable::parse('2026-05-01T00:00:00Z'),
            'last_authenticated_at' => null,
        ]);

        self::assertSame('used@example.test', $account->contactEmail());
    }

    /** Null is a real answer; inventing an address would be worse than saying so. */
    public function test_an_account_with_no_asserted_address_reports_null(): void
    {
        $account = Account::query()->create();
        $account->externalIdentities()->create([
            'provider' => 'workos', 'provider_subject' => 'silent',
            'verified_claims' => [], 'provenance' => [],
            'linked_at' => now(), 'last_authenticated_at' => now(),
        ]);

        self::assertNull($account->contactEmail());
    }

    /**
     * The address is metadata, not identity. Two accounts may hold the same
     * one, and asserting a new address must not move anybody.
     */
    public function test_the_address_never_merges_or_moves_an_account(): void
    {
        $token = $this->serviceToken('logres');

        $first = $this->withToken($token)
            ->postJson('/api/v1/accounts/resolve', $this->payload('subject_a', 'shared@example.test'))
            ->json('account.id');

        $second = $this->withToken($token)
            ->postJson('/api/v1/accounts/resolve', $this->payload('subject_b', 'shared@example.test'))
            ->json('account.id');

        self::assertNotSame($first, $second, 'equal emails must never merge identities');

        // The same subject with a changed address stays the same account.
        $again = $this->withToken($token)
            ->postJson('/api/v1/accounts/resolve', $this->payload('subject_a', 'moved@example.test'))
            ->assertOk()
            ->assertJsonPath('account.contact_email', 'moved@example.test')
            ->json('account.id');

        self::assertSame($first, $again);
    }

    /** @return array<string, mixed> */
    private function payload(string $subject, string $email): array
    {
        return ['external' => [
            'provider' => 'workos',
            'provider_subject' => $subject,
            'claims' => ['email' => $email, 'email_verified' => true, 'name' => 'Person'],
            'provenance' => [
                'issuer' => 'https://api.workos.com/',
                'audience' => 'client_test',
                'asserted_at' => CarbonImmutable::now()->toIso8601ZuluString(),
            ],
            'authenticated_at' => CarbonImmutable::now()->toIso8601ZuluString(),
        ]];
    }
}
