<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

use DomainException;

final class InvalidAuthenticationLifecycleTransition extends DomainException
{
    public function __construct(
        public readonly AuthenticationLifecycleState $from,
        public readonly AuthenticationLifecycleSignal $signal,
    ) {
        parent::__construct("Cannot apply {$signal->value} while authentication is {$from->value}.");
    }
}
