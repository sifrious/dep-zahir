<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

enum AuthenticationLifecycleSignal: string
{
    case LocalLogout = 'local_logout';
    case SessionExpired = 'session_expired';
    case AccountSuspended = 'account_suspended';
    case ProviderRevoked = 'provider_revoked';
    case EntitlementRevoked = 'entitlement_revoked';
    case RecoveryAccepted = 'recovery_accepted';
    case ZahirUnavailable = 'zahir_unavailable';
}
