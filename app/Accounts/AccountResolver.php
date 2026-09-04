<?php

namespace App\Accounts;

use App\Identity\VerifiedExternal;
use App\Models\Account;
use App\Models\AccountResolutionEvent;
use App\Models\ExternalIdentity;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;

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

                return new AccountResolution(
                    $identity->account_id,
                    $identity->account->status->value,
                    false,
                    $this->authenticationState($identity),
                );
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

                return new AccountResolution(
                    $existing->account_id,
                    $existing->account->status->value,
                    false,
                    $this->authenticationState($existing),
                );
            }

            $this->audit($verified, $account->id, 'created', $caller);

            return new AccountResolution(
                $account->id,
                $account->status->value,
                true,
                AuthenticationLifecycleState::Authenticated,
            );
        }, 3);
    }

    public function link(string $accountId, VerifiedExternal $verified, ?string $caller = null): AccountResolution
    {
        return DB::transaction(function () use ($accountId, $verified, $caller): AccountResolution {
            if ($verified->authenticatedAt->lt(now()->subSeconds((int) config('zahir.identity_link_max_age_seconds', 600)))
                || $verified->authenticatedAt->isFuture()) {
                throw new IdentityLinkRejected('Identity assertion is not fresh enough to link.');
            }

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

                $identity->update([
                    'verified_claims' => $verified->claims,
                    'provenance' => $verified->provenance,
                    'last_authenticated_at' => $verified->authenticatedAt,
                ]);
                $this->audit($verified, $accountId, 'link_replayed', $caller);

                return new AccountResolution(
                    $account->id,
                    $account->status->value,
                    false,
                    $this->authenticationState($identity),
                );
            }

            $this->createIdentity($account, $verified);
            $this->audit($verified, $accountId, 'linked', $caller);

            return new AccountResolution(
                $account->id,
                $account->status->value,
                false,
                $account->status === AccountStatus::Suspended
                    ? AuthenticationLifecycleState::Suspended
                    : AuthenticationLifecycleState::Authenticated,
            );
        }, 3);
    }

    public function unlink(
        string $accountId,
        string $provider,
        string $providerSubject,
        ?string $caller = null,
        ?string $acceptedRecoveryReference = null,
    ): IdentityUnlinkResult {
        return DB::transaction(function () use ($accountId, $provider, $providerSubject, $caller, $acceptedRecoveryReference): IdentityUnlinkResult {
            $account = Account::query()->lockForUpdate()->findOrFail($accountId);
            $identity = $account->externalIdentities()
                ->where('provider', $provider)
                ->where('provider_subject', $providerSubject)
                ->lockForUpdate()
                ->first();

            if ($identity === null) {
                $this->auditSubject($accountId, $provider, $providerSubject, 'unlink_unchanged', $caller);

                return new IdentityUnlinkResult($accountId, 'unchanged');
            }

            if ($account->externalIdentities()->count() === 1 && $acceptedRecoveryReference === null) {
                throw new IdentityLinkRejected('The last usable identity requires an accepted recovery path.');
            }

            $identity->delete();
            $this->auditSubject($accountId, $provider, $providerSubject, 'unlinked', $caller, $acceptedRecoveryReference);

            return new IdentityUnlinkResult($accountId, 'unlinked');
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

    private function authenticationState(ExternalIdentity $identity): AuthenticationLifecycleState
    {
        if ($identity->account->status === AccountStatus::Suspended) {
            return AuthenticationLifecycleState::Suspended;
        }

        return $identity->status === ExternalIdentityStatus::Revoked
            ? AuthenticationLifecycleState::ProviderRevoked
            : AuthenticationLifecycleState::Authenticated;
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

    private function auditSubject(
        string $accountId,
        string $provider,
        string $providerSubject,
        string $outcome,
        ?string $caller,
        ?string $acceptedRecoveryReference = null,
    ): void {
        AccountResolutionEvent::query()->create([
            'account_id' => $accountId,
            'provider' => $provider,
            'provider_subject_hash' => hash('sha256', $providerSubject),
            'outcome' => $outcome,
            'caller' => $caller,
            'provenance' => $acceptedRecoveryReference === null
                ? []
                : ['accepted_recovery_reference' => $acceptedRecoveryReference],
            'occurred_at' => now(),
        ]);
    }
}
