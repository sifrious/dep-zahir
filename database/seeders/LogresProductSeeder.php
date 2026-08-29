<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class LogresProductSeeder extends Seeder
{
    public const DEVELOPMENT_ACCOUNT_ID = 'acc_01j6g000000000000000000000';

    public function run(): void
    {
        $product = Product::query()->updateOrCreate(
            ['key' => 'logres'],
            ['name' => (string) config('zahir.products.logres.name'), 'active' => true],
        );

        if (! config('zahir.seed_development_grants')) {
            return;
        }

        $account = Account::query()->find(self::DEVELOPMENT_ACCOUNT_ID);
        if ($account === null) {
            $account = new Account;
            $account->id = self::DEVELOPMENT_ACCOUNT_ID;
            $account->save();
        }

        EntitlementGrant::query()->updateOrCreate([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'entitlement' => (string) config('zahir.products.logres.access_entitlement'),
            'source' => 'development_seed',
            'source_reference' => 'zahir-011-v1',
        ], [
            'starts_at' => null,
            'expires_at' => null,
            'revoked_at' => null,
        ]);
    }
}
