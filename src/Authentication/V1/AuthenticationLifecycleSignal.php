<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

enum AuthenticationLifecycleSignal: string
{
    case LocalLogout = 'local_logout';
    case SessionExpired = 'session_expired';
    case AccountSuspended = 'account_suspended';
    case AccountReactivated = 'account_reactivated';
    case ProviderRevoked = 'provider_revoked';
    case EntitlementRevoked = 'entitlement_revoked';
    case RecoveryStarted = 'recovery_started';
    case RecoveryAccepted = 'recovery_accepted';
    case Reauthenticated = 'reauthenticated';
    case ZahirUnavailable = 'zahir_unavailable';
}
