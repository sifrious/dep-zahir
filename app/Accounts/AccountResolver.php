<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\ExternalIdentity;
use Illuminate\Support\Facades\DB;

final readonly class AccountResolver
{
    public function resolve(string $issuer, string $subject): Account
    {
        return DB::transaction(function () use ($issuer, $subject): Account {
            $identity = ExternalIdentity::query()
                ->where('issuer', $issuer)
                ->where('subject', $subject)
                ->first();

            if ($identity !== null) {
                return $identity->account;
            }

            $account = Account::query()->create();

            $account->externalIdentities()->create([
                'issuer' => $issuer,
                'subject' => $subject,
                'last_authenticated_at' => now(),
            ]);

            return $account;
        });
    }
}
