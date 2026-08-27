<?php

namespace Tests\Feature;

use App\Accounts\AccountResolver;
use App\Models\Account;
use App\Models\ExternalIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_external_identity_resolves_idempotently_to_one_account(): void
    {
        $resolver = $this->app->make(AccountResolver::class);

        $first = $resolver->resolve('https://identity.example', 'person-123');
        $second = $resolver->resolve('https://identity.example', 'person-123');

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Account::query()->count());
        $this->assertSame(1, ExternalIdentity::query()->count());
    }

    public function test_subject_identity_is_scoped_to_its_issuer(): void
    {
        $resolver = $this->app->make(AccountResolver::class);

        $first = $resolver->resolve('https://first.example', 'person-123');
        $second = $resolver->resolve('https://second.example', 'person-123');

        $this->assertFalse($first->is($second));
        $this->assertSame(2, Account::query()->count());
        $this->assertSame(2, ExternalIdentity::query()->count());
    }
}
