<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\EntitlementGrant;
use Carbon\CarbonImmutable;

final readonly class EntitlementDecider
{
    public function decide(
        Account $account,
        string $product,
        string $entitlement,
        ?CarbonImmutable $evaluatedAt = null,
    ): EntitlementDecision {
        $evaluatedAt ??= CarbonImmutable::now();

        $grant = $account->status === AccountStatus::Active
            ? EntitlementGrant::query()
                ->whereBelongsTo($account)
                ->whereHas('product', fn ($query) => $query
                    ->where('key', $product)
                    ->where('active', true))
                ->where('entitlement', $entitlement)
                ->whereNull('revoked_at')
                ->where(fn ($query) => $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $evaluatedAt))
                ->where(fn ($query) => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $evaluatedAt))
                ->oldest('created_at')
                ->first()
            : null;

        return new EntitlementDecision(
            allowed: $grant !== null,
            accountId: $account->getKey(),
            product: $product,
            entitlement: $entitlement,
            evaluatedAt: $evaluatedAt,
            grantId: $grant?->getKey(),
        );
    }
}
