<?php

namespace App\Accounts;

use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;

final readonly class AccountResolution
{
    public function __construct(
        public string $accountId,
        public string $status,
        public bool $created,
        public AuthenticationLifecycleState $authenticationState,
    ) {}
}
