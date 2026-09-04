<?php

namespace App\Accounts;

final readonly class ExternalIdentityLifecycleResult
{
    public function __construct(
        public string $accountId,
        public ExternalIdentityStatus $identityStatus,
        public string $result,
        public bool $replayed,
    ) {}
}
