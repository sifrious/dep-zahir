<?php

namespace App\Accounts;

final readonly class AccountResolution
{
    public function __construct(
        public string $accountId,
        public string $status,
        public bool $created,
    ) {}
}
