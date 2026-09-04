<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

final readonly class AuthenticationLifecycleTransition
{
    public function __construct(
        public AuthenticationLifecycleState $from,
        public AuthenticationLifecycleState $to,
        public AuthenticationLifecycleSignal $signal,
        public bool $invalidatesLocalSession,
        public AuthenticationNextAction $nextAction,
        public bool $replayed,
    ) {}
}
