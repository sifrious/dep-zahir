<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Idempotent provisioning for one product declared in `config('zahir.products')`.
 *
 * Every product is provisioned the same way, so a second consumer cannot drift
 * into slightly different semantics from the first — which is exactly how one
 * product's access quietly becomes another's.
 *
 * Production stays deny-by-default. The development grant is opt-in through
 * `ZAHIR_SEED_DEVELOPMENT_GRANTS`, and it targets a deterministic account so
 * fixtures are reproducible without inventing identities per machine.
 */
abstract class ProductSeeder extends Seeder
{
    abstract protected function productKey(): string;

    public function run(): void
    {
        $key = $this->productKey();
        $settings = config("zahir.products.{$key}");

        if (! is_array($settings)) {
            throw new RuntimeException("Product [{$key}] is not declared in config/zahir.php.");
        }

        // updateOrCreate on the unique key is what makes re-running safe: a
        // repeated deploy refreshes the product rather than duplicating it.
        $product = Product::query()->updateOrCreate(
            ['key' => $key],
            ['name' => (string) $settings['name'], 'active' => true],
        );

        if (! config('zahir.seed_development_grants')) {
            return;
        }

        $accountId = (string) $settings['development_account_id'];
        $account = Account::query()->find($accountId);
        if ($account === null) {
            $account = new Account;
            $account->id = $accountId;
            $account->save();
        }

        EntitlementGrant::query()->updateOrCreate([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'entitlement' => (string) $settings['access_entitlement'],
            'source' => 'development_seed',
            'source_reference' => (string) $settings['seed_reference'],
        ], [
            'starts_at' => null,
            'expires_at' => null,
            'revoked_at' => null,
        ]);
    }
}
