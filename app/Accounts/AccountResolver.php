<?php

namespace App\Accounts;

use App\Identity\VerifiedExternal;
use App\Models\Account;
use App\Models\AccountResolutionEvent;
use App\Models\ExternalIdentity;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class AccountResolver
{
    public function resolve(VerifiedExternal $verified, ?string $caller = null): AccountResolution
    {
        return DB::transaction(function () use ($verified, $caller): AccountResolution {
            $identities = $this->identities($verified);

            if ($identities->count() > 1) {
                $this->audit($verified, null, 'ambiguous', $caller);
                throw IdentityCollision::ambiguous();
            }

            if ($identity = $identities->first()) {
                $identity->update([
                    'verified_claims' => $verified->claims,
                    'provenance' => $verified->provenance,
                    'last_authenticated_at' => $verified->authenticatedAt,
                ]);
                $this->audit($verified, $identity->account_id, 'resolved', $caller);

                return new AccountResolution($identity->account_id, $identity->account->status->value, false);
            }

            $account = Account::query()->create();

            try {
                $this->createIdentity($account, $verified);
            } catch (QueryException $exception) {
                $existing = $this->identities($verified)->first();
                if ($existing === null) {
                    throw $exception;
                }
                $account->delete();
                $this->audit($verified, $existing->account_id, 'resolved_after_race', $caller);

                return new AccountResolution($existing->account_id, $existing->account->status->value, false);
            }

            $this->audit($verified, $account->id, 'created', $caller);

            return new AccountResolution($account->id, $account->status->value, true);
        }, 3);
    }

    public function link(string $accountId, VerifiedExternal $verified, ?string $caller = null): AccountResolution
    {
        return DB::transaction(function () use ($accountId, $verified, $caller): AccountResolution {
            $account = Account::query()->lockForUpdate()->findOrFail($accountId);
            $identities = $this->identities($verified);

            if ($identities->count() > 1) {
                $this->audit($verified, $accountId, 'ambiguous', $caller);
                throw IdentityCollision::ambiguous();
            }

            if ($identity = $identities->first()) {
                if ($identity->account_id !== $accountId) {
                    $this->audit($verified, $accountId, 'link_collision', $caller);
                    throw IdentityCollision::linkedElsewhere();
                }

                return new AccountResolution($account->id, $account->status->value, false);
            }

            $this->createIdentity($account, $verified);
            $this->audit($verified, $accountId, 'linked', $caller);

            return new AccountResolution($account->id, $account->status->value, false);
        }, 3);
    }

    /** @return Collection<int, ExternalIdentity> */
    private function identities(VerifiedExternal $verified): Collection
    {
        return ExternalIdentity::query()
            ->where('provider', $verified->provider)
            ->where('provider_subject', $verified->providerSubject)
            ->limit(2)
            ->get();
    }

    private function createIdentity(Account $account, VerifiedExternal $verified): void
    {
        $account->externalIdentities()->create([
            'provider' => $verified->provider,
            'provider_subject' => $verified->providerSubject,
            'verified_claims' => $verified->claims,
            'provenance' => $verified->provenance,
            'linked_at' => now(),
            'last_authenticated_at' => $verified->authenticatedAt,
        ]);
    }

    private function audit(VerifiedExternal $verified, ?string $accountId, string $outcome, ?string $caller): void
    {
        AccountResolutionEvent::query()->create([
            'account_id' => $accountId,
            'provider' => $verified->provider,
            'provider_subject_hash' => hash('sha256', $verified->providerSubject),
            'outcome' => $outcome,
            'caller' => $caller,
            'provenance' => $verified->provenance,
            'occurred_at' => now(),
        ]);
    }
}
