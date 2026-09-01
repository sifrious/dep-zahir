<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Database\Seeders\BurdgenProductSeeder;
use Database\Seeders\LogresProductSeeder;
use Database\Seeders\MaryWinProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Burdgen is the second consumer of shared authentication, and the first real
 * test of whether product access is genuinely per product.
 */
final class BurdgenProductEntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_burdgen_contract_identifiers_are_stable(): void
    {
        self::assertSame('Burdgen', config('zahir.products.burdgen.name'));
        self::assertSame('access', config('zahir.products.burdgen.access_entitlement'));
        self::assertSame('mme-2102-v1', config('zahir.products.burdgen.seed_reference'));

        // Production stays shut until the launch access policy is decided.
        self::assertSame('deny_until_launch_policy_approved', config('zahir.products.burdgen.production_grant_policy'));
        self::assertSame('launch_access_administrator', config('zahir.products.burdgen.grant_owner_role'));
        self::assertSame('manual_invitation_registry', config('zahir.products.burdgen.revocation_source'));
    }

    public function test_provisioning_is_idempotent_and_production_grants_nothing(): void
    {
        config(['zahir.seed_development_grants' => false]);
        $this->seed(BurdgenProductSeeder::class);
        $this->seed(BurdgenProductSeeder::class);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', ['key' => 'burdgen', 'name' => 'Burdgen', 'active' => true]);
        $this->assertDatabaseCount('entitlement_grants', 0);
    }

    public function test_the_development_grant_is_deterministic_and_repeatable(): void
    {
        config(['zahir.seed_development_grants' => true]);
        $this->seed(BurdgenProductSeeder::class);
        $this->seed(BurdgenProductSeeder::class);

        $this->assertDatabaseCount('entitlement_grants', 1);
        $this->assertDatabaseHas('entitlement_grants', [
            'account_id' => BurdgenProductSeeder::DEVELOPMENT_ACCOUNT_ID,
            'entitlement' => 'access',
            'source' => 'development_seed',
            'source_reference' => 'mme-2102-v1',
        ]);

        $this->assertNotSame(
            LogresProductSeeder::DEVELOPMENT_ACCOUNT_ID,
            BurdgenProductSeeder::DEVELOPMENT_ACCOUNT_ID,
            'Each product seeds its own account so one fixture cannot mask the other.',
        );
    }

    public function test_seeding_both_products_creates_two_products_and_two_separate_grants(): void
    {
        config(['zahir.seed_development_grants' => true]);
        $this->seed(LogresProductSeeder::class);
        $this->seed(BurdgenProductSeeder::class);
        $this->seed(LogresProductSeeder::class);
        $this->seed(BurdgenProductSeeder::class);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('entitlement_grants', 2);
    }

    /**
     * The heart of MME-2102. A grant is for one product. If holding Logres
     * access opened Burdgen, entitlements would be decoration and the second
     * consumer would have proved nothing.
     */
    public function test_a_logres_grant_never_opens_burdgen_and_the_reverse(): void
    {
        $token = $this->serviceToken('burdgen');
        $logres = Product::query()->create(['key' => 'logres', 'name' => 'Logres', 'active' => true]);
        $burdgen = Product::query()->create(['key' => 'burdgen', 'name' => 'Burdgen', 'active' => true]);

        $logresOnly = Account::query()->create();
        EntitlementGrant::query()->create([
            'account_id' => $logresOnly->id, 'product_id' => $logres->id,
            'entitlement' => 'access', 'source' => 'test',
        ]);

        $burdgenOnly = Account::query()->create();
        EntitlementGrant::query()->create([
            'account_id' => $burdgenOnly->id, 'product_id' => $burdgen->id,
            'entitlement' => 'access', 'source' => 'test',
        ]);

        $this->decide($token, $logresOnly->id, 'burdgen')->assertJsonPath('allowed', false);
        $this->decide($token, $logresOnly->id, 'logres')->assertJsonPath('allowed', true);
        $this->decide($token, $burdgenOnly->id, 'logres')->assertJsonPath('allowed', false);
        $this->decide($token, $burdgenOnly->id, 'burdgen')->assertJsonPath('allowed', true);
    }

    /**
     * Zahir decides on grants alone. It has no notion of a product's local
     * roles, preferences, onboarding progress, or connected providers, so none
     * of them can be argued into an entitlement — which is why product-local
     * state can never elevate global access.
     */
    public function test_an_account_with_history_but_no_grant_is_denied(): void
    {
        $token = $this->serviceToken('burdgen');
        Product::query()->create(['key' => 'burdgen', 'name' => 'Burdgen', 'active' => true]);

        $account = Account::query()->create();
        $account->externalIdentities()->create([
            'provider' => 'workos',
            'provider_subject' => 'user_no_grant',
            'verified_claims' => ['email' => 'person@example.test'],
            'provenance' => [],
            'linked_at' => now(),
        ]);

        $this->decide($token, $account->id, 'burdgen')
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('account_status', 'active')
            ->assertJsonPath('grant_id', null);
    }

    public function test_suspension_and_revocation_both_fail_closed_for_burdgen(): void
    {
        $token = $this->serviceToken('burdgen');
        $burdgen = Product::query()->create(['key' => 'burdgen', 'name' => 'Burdgen', 'active' => true]);
        $account = Account::query()->create();
        $grant = EntitlementGrant::query()->create([
            'account_id' => $account->id, 'product_id' => $burdgen->id,
            'entitlement' => 'access', 'source' => 'test',
        ]);

        $this->decide($token, $account->id, 'burdgen')->assertJsonPath('allowed', true);

        $account->update(['status' => 'suspended']);
        $this->decide($token, $account->id, 'burdgen')->assertJsonPath('allowed', false);

        $account->update(['status' => 'active']);
        $grant->forceFill(['revoked_at' => CarbonImmutable::now()])->save();
        $this->decide($token, $account->id, 'burdgen')->assertJsonPath('allowed', false);

        // An inactive product closes too, without touching any account.
        $grant->forceFill(['revoked_at' => null])->save();
        $burdgen->update(['active' => false]);
        $this->decide($token, $account->id, 'burdgen')->assertJsonPath('allowed', false);
    }

    public function test_the_entitlement_contract_requires_an_authenticated_caller(): void
    {
        Product::query()->create(['key' => 'burdgen', 'name' => 'Burdgen', 'active' => true]);
        $account = Account::query()->create();

        $this->postJson('/api/v1/entitlements/decide', [
            'account_id' => $account->id, 'product' => 'burdgen', 'entitlement' => 'access',
        ])->assertUnauthorized();
    }

    private function decide(string $token, string $accountId, string $product): TestResponse
    {
        return $this->withToken($token)->postJson('/api/v1/entitlements/decide', [
            'account_id' => $accountId,
            'product' => $product,
            'entitlement' => 'access',
        ])->assertOk();
    }

    /**
     * A third product is where "per product" stops being a coincidence. Each
     * pair must open exactly one door, in every direction.
     */
    public function test_three_products_stay_mutually_exclusive(): void
    {
        $token = $this->serviceToken('caller');
        $keys = ['logres', 'burdgen', 'mary-win'];

        $products = [];
        foreach ($keys as $key) {
            $products[$key] = Product::query()->create(['key' => $key, 'name' => $key, 'active' => true]);
        }

        $accounts = [];
        foreach ($keys as $key) {
            $account = Account::query()->create();
            EntitlementGrant::query()->create([
                'account_id' => $account->id, 'product_id' => $products[$key]->id,
                'entitlement' => 'access', 'source' => 'test',
            ]);
            $accounts[$key] = $account;
        }

        foreach ($keys as $held) {
            foreach ($keys as $asked) {
                $allowed = (bool) $this->decide($token, $accounts[$held]->id, $asked)->json('allowed');
                $this->assertSame(
                    $held === $asked,
                    $allowed,
                    "an account holding {$held}.access must ".($held === $asked ? 'reach' : 'not reach')." {$asked}",
                );
            }
        }
    }

    public function test_the_mary_win_contract_identifiers_are_stable(): void
    {
        self::assertSame('mary.win', config('zahir.products.mary-win.name'));
        self::assertSame('access', config('zahir.products.mary-win.access_entitlement'));
        self::assertSame('mary-win-v1', config('zahir.products.mary-win.seed_reference'));
        self::assertSame('deny_until_launch_policy_approved', config('zahir.products.mary-win.production_grant_policy'));
    }

    public function test_every_product_seeds_a_distinct_development_account(): void
    {
        config(['zahir.seed_development_grants' => true]);
        $this->seed(LogresProductSeeder::class);
        $this->seed(BurdgenProductSeeder::class);
        $this->seed(MaryWinProductSeeder::class);

        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('entitlement_grants', 3);
        // Distinct accounts, so no product's fixture can mask a bug in another's.
        $this->assertDatabaseCount('accounts', 3);
    }
}
