<?php

namespace Tests\Feature;

use App\Accounts\AccountResolver;
use App\Accounts\IdentityCollision;
use App\Identity\VerifiedExternal;
use App\Models\Account;
use App\Models\ExternalIdentity;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_replay_resolves_same_opaque_account_and_email_change_does_not_change_identity(): void
    {
        $resolver = app(AccountResolver::class);
        $first = $resolver->resolve($this->verified('subject-1', 'old@example.test'));
        $second = $resolver->resolve($this->verified('subject-1', 'new@example.test'));

        $this->assertSame($first->accountId, $second->accountId);
        $this->assertMatchesRegularExpression('/^acc_[0-9a-z]{26}$/', $first->accountId);
        $this->assertTrue($first->created);
        $this->assertFalse($second->created);
        $this->assertSame('new@example.test', ExternalIdentity::first()->verified_claims['email']);
        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_matching_email_addresses_never_implicitly_merge_accounts(): void
    {
        $resolver = app(AccountResolver::class);
        $first = $resolver->resolve($this->verified('subject-1', 'same@example.test'));
        $second = $resolver->resolve($this->verified('subject-2', 'same@example.test'));

        $this->assertNotSame($first->accountId, $second->accountId);
        $this->assertDatabaseCount('accounts', 2);
    }

    public function test_another_verified_identity_can_be_explicitly_linked(): void
    {
        $resolver = app(AccountResolver::class);
        $account = $resolver->resolve($this->verified('subject-1'));
        $linked = $resolver->link($account->accountId, $this->verified('subject-2', provider: 'future-idp'));

        $this->assertSame($account->accountId, $linked->accountId);
        $this->assertDatabaseCount('external_identities', 2);
    }

    public function test_linking_an_identity_owned_by_another_account_fails_closed(): void
    {
        $resolver = app(AccountResolver::class);
        $first = $resolver->resolve($this->verified('subject-1'));
        $resolver->resolve($this->verified('subject-2'));

        $this->expectException(IdentityCollision::class);
        $resolver->link($first->accountId, $this->verified('subject-2'));
    }

    public function test_provider_and_subject_are_unique_at_database_boundary(): void
    {
        $first = Account::query()->create();
        $second = Account::query()->create();
        $attributes = [
            'provider' => 'workos', 'provider_subject' => 'subject-1',
            'verified_claims' => [], 'provenance' => $this->provenance(),
            'linked_at' => now(), 'last_authenticated_at' => now(),
        ];
        $first->externalIdentities()->create($attributes);

        $this->expectException(QueryException::class);
        $second->externalIdentities()->create($attributes);
    }

    private function verified(string $subject, ?string $email = null, string $provider = 'workos'): VerifiedExternal
    {
        return new VerifiedExternal(
            $provider, $subject,
            array_filter(['email' => $email], fn ($value) => $value !== null),
            $this->provenance(),
            CarbonImmutable::parse('2026-08-29T12:00:00Z'),
        );
    }

    private function provenance(): array
    {
        return ['issuer' => 'https://api.workos.com/', 'audience' => 'client_123', 'asserted_at' => '2026-08-29T12:00:00Z'];
    }
}
