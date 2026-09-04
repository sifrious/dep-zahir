<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

final readonly class AuthenticationLifecycle
{
    public function transition(
        AuthenticationLifecycleState $from,
        AuthenticationLifecycleSignal $signal,
    ): AuthenticationLifecycleTransition {
        $to = $this->target($from, $signal);

        return new AuthenticationLifecycleTransition(
            from: $from,
            to: $to,
            signal: $signal,
            invalidatesLocalSession: in_array($signal, [
                AuthenticationLifecycleSignal::LocalLogout,
                AuthenticationLifecycleSignal::SessionExpired,
                AuthenticationLifecycleSignal::AccountSuspended,
                AuthenticationLifecycleSignal::ProviderRevoked,
                AuthenticationLifecycleSignal::EntitlementRevoked,
            ], true),
            nextAction: match ($to) {
                AuthenticationLifecycleState::Authenticated,
                AuthenticationLifecycleState::Recovered => AuthenticationNextAction::None,
                AuthenticationLifecycleState::LoggedOut,
                AuthenticationLifecycleState::Expired => AuthenticationNextAction::SignIn,
                AuthenticationLifecycleState::Suspended,
                AuthenticationLifecycleState::EntitlementRevoked => AuthenticationNextAction::ContactSupport,
                AuthenticationLifecycleState::ProviderRevoked,
                AuthenticationLifecycleState::RecoveryRequired => AuthenticationNextAction::RecoverExternalIdentity,
                AuthenticationLifecycleState::ZahirUnavailable => AuthenticationNextAction::Retry,
            },
            replayed: $from === $to,
        );
    }

    private function target(
        AuthenticationLifecycleState $from,
        AuthenticationLifecycleSignal $signal,
    ): AuthenticationLifecycleState {
        if ($signal === AuthenticationLifecycleSignal::ZahirUnavailable) {
            return AuthenticationLifecycleState::ZahirUnavailable;
        }

        return match ($signal) {
            AuthenticationLifecycleSignal::LocalLogout => $this->from(
                $from,
                [AuthenticationLifecycleState::Authenticated, AuthenticationLifecycleState::LoggedOut],
                AuthenticationLifecycleState::LoggedOut,
                $signal,
            ),
            AuthenticationLifecycleSignal::SessionExpired => $this->from(
                $from,
                [AuthenticationLifecycleState::Authenticated, AuthenticationLifecycleState::Expired],
                AuthenticationLifecycleState::Expired,
                $signal,
            ),
            AuthenticationLifecycleSignal::AccountSuspended => $this->from(
                $from,
                AuthenticationLifecycleState::cases(),
                AuthenticationLifecycleState::Suspended,
                $signal,
            ),
            AuthenticationLifecycleSignal::AccountReactivated => $this->from(
                $from,
                [AuthenticationLifecycleState::Suspended, AuthenticationLifecycleState::Recovered],
                AuthenticationLifecycleState::Recovered,
                $signal,
            ),
            AuthenticationLifecycleSignal::ProviderRevoked => $this->from(
                $from,
                [
                    AuthenticationLifecycleState::Authenticated,
                    AuthenticationLifecycleState::ProviderRevoked,
                    AuthenticationLifecycleState::RecoveryRequired,
                ],
                AuthenticationLifecycleState::ProviderRevoked,
                $signal,
            ),
            AuthenticationLifecycleSignal::EntitlementRevoked => $this->from(
                $from,
                [
                    AuthenticationLifecycleState::Authenticated,
                    AuthenticationLifecycleState::EntitlementRevoked,
                ],
                AuthenticationLifecycleState::EntitlementRevoked,
                $signal,
            ),
            AuthenticationLifecycleSignal::RecoveryStarted => $this->from(
                $from,
                [
                    AuthenticationLifecycleState::ProviderRevoked,
                    AuthenticationLifecycleState::RecoveryRequired,
                ],
                AuthenticationLifecycleState::RecoveryRequired,
                $signal,
            ),
            AuthenticationLifecycleSignal::RecoveryAccepted => $this->from(
                $from,
                [AuthenticationLifecycleState::RecoveryRequired, AuthenticationLifecycleState::Recovered],
                AuthenticationLifecycleState::Recovered,
                $signal,
            ),
            AuthenticationLifecycleSignal::Reauthenticated => $this->from(
                $from,
                [
                    AuthenticationLifecycleState::Authenticated,
                    AuthenticationLifecycleState::LoggedOut,
                    AuthenticationLifecycleState::Expired,
                    AuthenticationLifecycleState::Recovered,
                    AuthenticationLifecycleState::ZahirUnavailable,
                ],
                AuthenticationLifecycleState::Authenticated,
                $signal,
            ),
            AuthenticationLifecycleSignal::ZahirUnavailable => AuthenticationLifecycleState::ZahirUnavailable,
        };
    }

    /**
     * @param list<AuthenticationLifecycleState> $allowed
     */
    private function from(
        AuthenticationLifecycleState $from,
        array $allowed,
        AuthenticationLifecycleState $to,
        AuthenticationLifecycleSignal $signal,
    ): AuthenticationLifecycleState {
        if (! in_array($from, $allowed, true)) {
            throw new InvalidAuthenticationLifecycleTransition($from, $signal);
        }

        return $to;
    }
}
