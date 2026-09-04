<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\ExternalIdentityLifecycleEvent;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;

final readonly class ExternalIdentityLifecycle
{
    public function revoke(
        string $accountId,
        string $provider,
        string $providerSubject,
        string $caller,
        string $reasonCode,
    ): ExternalIdentityLifecycleResult {
        return $this->change(
            $accountId,
            $provider,
            $providerSubject,
            ExternalIdentityStatus::Revoked,
            $caller,
            $reasonCode,
        );
    }

    public function recover(
        string $accountId,
        string $provider,
        string $providerSubject,
        string $caller,
        string $reasonCode,
        string $acceptedRecoveryReference,
    ): ExternalIdentityLifecycleResult {
        if ($acceptedRecoveryReference === '') {
            throw new InvalidArgumentException('An accepted recovery reference is required.');
        }

        return $this->change(
            $accountId,
            $provider,
            $providerSubject,
            ExternalIdentityStatus::Active,
            $caller,
            $reasonCode,
            $acceptedRecoveryReference,
        );
    }

    private function change(
        string $accountId,
        string $provider,
        string $providerSubject,
        ExternalIdentityStatus $to,
        string $caller,
        string $reasonCode,
        ?string $acceptedRecoveryReference = null,
    ): ExternalIdentityLifecycleResult {
        return DB::transaction(function () use (
            $accountId,
            $provider,
            $providerSubject,
            $to,
            $caller,
            $reasonCode,
            $acceptedRecoveryReference,
        ): ExternalIdentityLifecycleResult {
            $account = Account::query()->lockForUpdate()->findOrFail($accountId);
            $identity = $account->externalIdentities()
                ->where('provider', $provider)
                ->where('provider_subject', $providerSubject)
                ->lockForUpdate()
                ->firstOrFail();
            $from = $identity->status;

            if ($from !== $to) {
                $identity->update([
                    'status' => $to,
                    'revoked_at' => $to === ExternalIdentityStatus::Revoked ? now() : $identity->revoked_at,
                    'recovered_at' => $to === ExternalIdentityStatus::Active ? now() : null,
                ]);
            }

            ExternalIdentityLifecycleEvent::query()->create([
                'account_id' => $accountId,
                'provider' => $provider,
                'provider_subject_hash' => hash('sha256', $providerSubject),
                'from_status' => $from->value,
                'to_status' => $to->value,
                'caller' => $caller,
                'reason_code' => $reasonCode,
                'recovery_reference_hash' => $acceptedRecoveryReference === null
                    ? null
                    : hash('sha256', $acceptedRecoveryReference),
                'occurred_at' => now(),
            ]);

            return new ExternalIdentityLifecycleResult(
                accountId: $identity->account_id,
                authenticationState: $to === ExternalIdentityStatus::Revoked
                    ? AuthenticationLifecycleState::ProviderRevoked
                    : AuthenticationLifecycleState::Recovered,
                replayed: $from === $to,
            );
        }, 3);
    }
}
