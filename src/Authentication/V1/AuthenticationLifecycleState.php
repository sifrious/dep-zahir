<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

enum AuthenticationLifecycleState: string
{
    case Authenticated = 'authenticated';
    case LoggedOut = 'logged_out';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case ProviderRevoked = 'provider_revoked';
    case EntitlementRevoked = 'entitlement_revoked';
    case RecoveryRequired = 'recovery_required';
    case Recovered = 'recovered';
    case ZahirUnavailable = 'zahir_unavailable';

    public function loginOutcome(): ?LoginOutcomeType
    {
        return match ($this) {
            self::Authenticated => LoginOutcomeType::Authenticated,
            self::Expired => LoginOutcomeType::Expired,
            self::Suspended => LoginOutcomeType::Suspended,
            self::ProviderRevoked, self::RecoveryRequired => LoginOutcomeType::ProviderFailed,
            self::EntitlementRevoked => LoginOutcomeType::UnauthorizedProduct,
            self::ZahirUnavailable => LoginOutcomeType::ZahirUnavailable,
            self::LoggedOut, self::Recovered => null,
        };
    }
}
