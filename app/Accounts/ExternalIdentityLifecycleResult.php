<?php

namespace App\Accounts;

use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;

final readonly class ExternalIdentityLifecycleResult
{
    public function __construct(
        public string $accountId,
        public AuthenticationLifecycleState $authenticationState,
        public bool $replayed,
    ) {}
}
