<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

use DateTimeImmutable;

final readonly class AuthenticationLifecycle
{
    public function loginOutcome(AuthenticationLifecycleSignal $signal): ?LoginOutcomeType
    {
        return match ($signal) {
            AuthenticationLifecycleSignal::SessionExpired => LoginOutcomeType::Expired,
            AuthenticationLifecycleSignal::AccountSuspended => LoginOutcomeType::Suspended,
            AuthenticationLifecycleSignal::ProviderRevoked => LoginOutcomeType::ProviderFailed,
            AuthenticationLifecycleSignal::EntitlementRevoked => LoginOutcomeType::UnauthorizedProduct,
            AuthenticationLifecycleSignal::ZahirUnavailable => LoginOutcomeType::ZahirUnavailable,
            AuthenticationLifecycleSignal::LocalLogout,
            AuthenticationLifecycleSignal::RecoveryAccepted => null,
        };
    }

    public function sessionInvalidation(
        AuthenticationLifecycleSignal $signal,
        GlobalAccountIdentity $globalAccount,
        string $product,
        DateTimeImmutable $occurredAt,
    ): ?SessionInvalidation {
        $reason = match ($signal) {
            AuthenticationLifecycleSignal::LocalLogout => SessionInvalidationReason::Logout,
            AuthenticationLifecycleSignal::SessionExpired => SessionInvalidationReason::SessionExpired,
            AuthenticationLifecycleSignal::AccountSuspended => SessionInvalidationReason::AccountSuspended,
            AuthenticationLifecycleSignal::ProviderRevoked => SessionInvalidationReason::ProviderRevoked,
            AuthenticationLifecycleSignal::EntitlementRevoked => SessionInvalidationReason::EntitlementRevoked,
            AuthenticationLifecycleSignal::RecoveryAccepted,
            AuthenticationLifecycleSignal::ZahirUnavailable => null,
        };

        if ($reason === null) {
            return null;
        }

        return new SessionInvalidation($globalAccount, $product, $reason, $occurredAt);
    }
}
