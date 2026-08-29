<?php

namespace Tests\Feature;

use Database\Seeders\LogresProductSeeder;
use Database\Seeders\ReleaseRehearsalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogresProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_bootstrap_is_idempotent_and_production_denies_without_a_grant(): void
    {
        config(['zahir.seed_development_grants' => false]);
        $this->seed(LogresProductSeeder::class);
        $this->seed(LogresProductSeeder::class);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', ['key' => 'logres', 'name' => 'Logres', 'active' => true]);
        $this->assertDatabaseCount('entitlement_grants', 0);
        self::assertSame('deny_until_launch_policy_approved', config('zahir.products.logres.production_grant_policy'));
        self::assertSame('launch_access_administrator', config('zahir.products.logres.grant_owner_role'));
        self::assertSame('manual_invitation_registry', config('zahir.products.logres.revocation_source'));
    }

    public function test_development_grant_is_deterministic_idempotent_and_uses_canonical_entitlement(): void
    {
        config(['zahir.seed_development_grants' => true]);
        $this->seed(LogresProductSeeder::class);
        $this->seed(LogresProductSeeder::class);

        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('entitlement_grants', 1);
        $this->assertDatabaseHas('entitlement_grants', [
            'account_id' => LogresProductSeeder::DEVELOPMENT_ACCOUNT_ID,
            'entitlement' => 'access',
            'source' => 'development_seed',
            'source_reference' => 'zahir-011-v1',
        ]);
    }

    public function test_release_rehearsal_fixture_covers_restored_authoritative_data(): void
    {
        $this->seed(ReleaseRehearsalSeeder::class);
        $this->seed(ReleaseRehearsalSeeder::class);

        foreach ([
            'accounts',
            'external_identities',
            'entitlement_grants',
            'account_resolution_events',
            'account_lifecycle_events',
            'service_request_events',
        ] as $table) {
            $this->assertDatabaseCount($table, 1);
        }
    }
}
