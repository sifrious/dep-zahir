<?php

namespace Tests\Feature;

use App\Accounts\EntitlementDecider;
use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementDeciderTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_grant_allows_product_access(): void
    {
        $now = CarbonImmutable::parse('2026-08-27T12:00:00Z');
        [$account, $product] = $this->accountAndProduct();
        $grant = $this->grant($account, $product, startsAt: $now->subDay(), expiresAt: $now->addDay());

        $decision = $this->app->make(EntitlementDecider::class)
            ->decide($account, 'logres', 'access', $now);

        $this->assertTrue($decision->allowed);
        $this->assertSame($grant->getKey(), $decision->grantId);
    }

    public function test_expired_future_and_revoked_grants_deny_product_access(): void
    {
        $now = CarbonImmutable::parse('2026-08-27T12:00:00Z');
        [$account, $product] = $this->accountAndProduct();

        $this->grant($account, $product, expiresAt: $now);
        $this->grant($account, $product, startsAt: $now->addSecond());
        $this->grant($account, $product, revokedAt: $now->subSecond());

        $decision = $this->app->make(EntitlementDecider::class)
            ->decide($account, 'logres', 'access', $now);

        $this->assertFalse($decision->allowed);
        $this->assertNull($decision->grantId);
    }

    public function test_inactive_products_and_suspended_accounts_deny_access(): void
    {
        $now = CarbonImmutable::parse('2026-08-27T12:00:00Z');
        [$account, $product] = $this->accountAndProduct();
        $this->grant($account, $product);

        $product->update(['active' => false]);

        $this->assertFalse($this->app->make(EntitlementDecider::class)
            ->decide($account, 'logres', 'access', $now)->allowed);

        $product->update(['active' => true]);
        $account->update(['status' => 'suspended']);

        $this->assertFalse($this->app->make(EntitlementDecider::class)
            ->decide($account, 'logres', 'access', $now)->allowed);
    }

    private function accountAndProduct(): array
    {
        return [
            Account::query()->create(),
            Product::query()->create([
                'key' => 'logres',
                'name' => 'Logres',
            ]),
        ];
    }

    private function grant(
        Account $account,
        Product $product,
        ?CarbonImmutable $startsAt = null,
        ?CarbonImmutable $expiresAt = null,
        ?CarbonImmutable $revokedAt = null,
    ): EntitlementGrant {
        return EntitlementGrant::query()->create([
            'account_id' => $account->getKey(),
            'product_id' => $product->getKey(),
            'entitlement' => 'access',
            'source' => 'manual',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'revoked_at' => $revokedAt,
        ]);
    }
}
