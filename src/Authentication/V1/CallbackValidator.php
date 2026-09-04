<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

use DateTimeImmutable;

final class CallbackValidator
{
    /**
     * State records must be atomically consumed by the caller after this check.
     *
     * @throws AuthenticationFailure
     */
    public function validate(
        AuthenticationCallback $callback,
        PendingAuthentication $pending,
        DateTimeImmutable $now,
    ): void {
        if (! hash_equals($pending->state, $callback->state)) {
            throw new AuthenticationFailure(FailureCode::StateMismatch);
        }

        if ($pending->expiresAt <= $now) {
            throw new AuthenticationFailure(FailureCode::ExpiredToken);
        }

        if ($callback->error !== null) {
            throw new AuthenticationFailure(
                $callback->error === 'access_denied'
                    ? FailureCode::Canceled
                    : FailureCode::ProviderFailed,
            );
        }
    }
}
