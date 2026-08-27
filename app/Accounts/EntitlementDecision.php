<?php

namespace App\Accounts;

use Carbon\CarbonImmutable;

final readonly class EntitlementDecision
{
    public function __construct(
        public bool $allowed,
        public string $accountId,
        public string $product,
        public string $entitlement,
        public CarbonImmutable $evaluatedAt,
        public ?string $grantId,
    ) {}
}
