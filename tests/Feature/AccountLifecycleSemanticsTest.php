<?php

namespace Tests\Feature;

use App\Accounts\AccountResolver;
use App\Identity\VerifiedExternal;
use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lifecycle semantics a product depends on: repeated requests are safe, denials
 * fail closed, and none of it destroys anything the product still needs.
 */
final class AccountLifecycleSemanticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Retry safety. A lifecycle call may be repeated by an impatient operator or
     * a retried job, and neither may produce a second state change — but each
     * attempt must still be attributable, so the audit trail keeps growing.
     */
    public function test_repeating_a_lifecycle_request_is_deterministic_and_still_audited(): void
    {
        $admin = $this->serviceToken('account-admin', true);
        $account = Account::query()->create();
        $url = "/api/v1/accounts/{$account->id}/suspension";

        foreach (range(1, 3) as $attempt) {
            $this->withToken($admin)->postJson($url, ['reason' => 'risk review'])
                ->assertOk()
                ->assertJsonPath('account.status', 'suspended');
        }

        $this->assertSame('suspended', $account->fresh()?->status->value);
        $this->assertDatabaseCount('account_lifecycle_events', 3);

        foreach (range(1, 2) as $attempt) {
            $this->withToken($admin)->deleteJson($url, ['reason' => 'review completed'])
                ->assertOk()
                ->assertJsonPath('account.status', 'active');
        }

        $this->assertSame('active', $account->fresh()?->status->value);
        $this->assertDatabaseCount('account_lifecycle_events', 5);
    }

    /**
     * Suspension is an access decision, not a delete. The account, its identity
     * mappings, and its grants all survive so that reactivation restores exactly
     * what was there — and so a product's own data is never orphaned.
     */
    public function test_suspension_denies_access_without_destroying_anything(): void
    {
        $admin = $this->serviceToken('account-admin', true);
        $product = $this->serviceToken('logres');

        $account = $this->resolved('user_suspend');
        $logres = Product::query()->create(['key' => 'logres', 'name' => 'Logres', 'active' => true]);
        EntitlementGrant::query()->create([
            'account_id' => $account->id,
            'product_id' => $logres->id,
            'entitlement' => 'access',
            'source' => 'test',
        ]);

        $decide = fn (): bool => (bool) $this->withToken($product)
            ->postJson('/api/v1/entitlements/decide', [
                'account_id' => $account->id, 'product' => 'logres', 'entitlement' => 'access',
            ])->json('allowed');

        $this->assertTrue($decide());

        $this->withToken($admin)
            ->postJson("/api/v1/accounts/{$account->id}/suspension", ['reason' => 'risk review'])
            ->assertOk();

        $this->assertFalse($decide(), 'A suspended account must fail closed.');

        // Nothing was deleted; the grant and identity mapping are intact.
        $this->assertDatabaseCount('entitlement_grants', 1);
        $this->assertDatabaseCount('external_identities', 1);
        $this->assertDatabaseHas('accounts', ['id' => $account->id]);

        $this->withToken($admin)
            ->deleteJson("/api/v1/accounts/{$account->id}/suspension", ['reason' => 'cleared'])
            ->assertOk();

        $this->assertTrue($decide(), 'Reactivation must restore the pre-existing grant.');
    }

    /**
     * Revoking the grant, rather than the account, must also fail closed — and
     * must leave the account itself usable for every other product.
     */
    public function test_revoking_a_grant_fails_closed_and_leaves_the_account_intact(): void
    {
        $product = $this->serviceToken('logres');
        $account = $this->resolved('user_revoke');
        $logres = Product::query()->create(['key' => 'logres', 'name' => 'Logres', 'active' => true]);
        $grant = EntitlementGrant::query()->create([
            'account_id' => $account->id,
            'product_id' => $logres->id,
            'entitlement' => 'access',
            'source' => 'test',
        ]);

        $decide = fn (): array => (array) $this->withToken($product)
            ->postJson('/api/v1/entitlements/decide', [
                'account_id' => $account->id, 'product' => 'logres', 'entitlement' => 'access',
            ])->json();

        $this->assertTrue($decide()['allowed']);

        $grant->forceFill(['revoked_at' => CarbonImmutable::now()])->save();

        $denied = $decide();
        $this->assertFalse($denied['allowed']);
        $this->assertNull($denied['grant_id']);
        // The account stays active: this product is closed, others are not.
        $this->assertSame('active', $denied['account_status']);
    }

    /**
     * Recovery needs a stable code. Without one, a product cannot tell "offer an
     * account-recovery path" from "something broke", and the only honest thing
     * it could render is a generic error.
     */
    public function test_removing_the_last_identity_reports_a_stable_recovery_reason(): void
    {
        $token = $this->serviceToken('logres');
        $account = $this->resolved('user_only');

        $this->withToken($token)
            ->withHeader('X-Zahir-Current-Account', $account->id)
            ->deleteJson("/api/v1/accounts/{$account->id}/identities", [
                'provider' => 'workos',
                'provider_subject' => 'user_only',
            ])
            ->assertStatus(409)
            ->assertJsonPath('reason', 'recovery_required');

        // The identity is still there: a refused unlink changes nothing.
        $this->assertDatabaseCount('external_identities', 1);
    }

    private function resolved(string $subject): Account
    {
        $resolution = app(AccountResolver::class)->resolve($this->verified($subject));

        return Account::query()->findOrFail($resolution->accountId);
    }

    private function verified(string $subject): VerifiedExternal
    {
        return new VerifiedExternal(
            provider: 'workos',
            providerSubject: $subject,
            claims: ['email' => 'person@example.test', 'email_verified' => true, 'name' => 'Person'],
            provenance: [
                'issuer' => 'https://api.workos.com/',
                'audience' => 'client_test',
                'asserted_at' => CarbonImmutable::now()->toIso8601ZuluString(),
            ],
            authenticatedAt: CarbonImmutable::now(),
        );
    }
}
