<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycle;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleSignal;
use Sifrious\Zahir\Authentication\V1\GlobalAccountIdentity;

final class AuthenticationLifecycleTest extends TestCase
{
    public function test_fixture_backed_lifecycle_uses_frozen_outcomes_and_session_invalidation(): void
    {
        $lifecycle = new AuthenticationLifecycle;
        $globalAccount = new GlobalAccountIdentity('acc_01FIXTUREACCOUNT');
        $occurredAt = new DateTimeImmutable('2026-09-04T12:00:00Z');

        foreach ($this->fixtures()['cases'] as $name => $case) {
            $signal = AuthenticationLifecycleSignal::from($case['signal']);
            $outcome = $lifecycle->loginOutcome($signal);
            $invalidation = $lifecycle->sessionInvalidation(
                $signal,
                $globalAccount,
                'logres',
                $occurredAt,
            );

            $this->assertSame(
                $case['expected']['login_outcome'],
                $outcome?->value,
                $name,
            );
            $this->assertSame(
                $case['expected']['session_invalidation_reason'],
                $invalidation?->reason->value,
                $name,
            );

            if ($invalidation !== null) {
                $this->assertSame($globalAccount, $invalidation->globalAccount, $name);
                $this->assertSame('logres', $invalidation->product, $name);
            }
        }
    }

    public function test_provider_revocation_and_zahir_unavailability_remain_distinct(): void
    {
        $lifecycle = new AuthenticationLifecycle;
        $globalAccount = new GlobalAccountIdentity('acc_01FIXTUREACCOUNT');
        $occurredAt = new DateTimeImmutable('2026-09-04T12:00:00Z');

        $providerOutcome = $lifecycle->loginOutcome(
            AuthenticationLifecycleSignal::ProviderRevoked,
        );
        $unavailableOutcome = $lifecycle->loginOutcome(
            AuthenticationLifecycleSignal::ZahirUnavailable,
        );
        $providerInvalidation = $lifecycle->sessionInvalidation(
            AuthenticationLifecycleSignal::ProviderRevoked,
            $globalAccount,
            'logres',
            $occurredAt,
        );
        $unavailableInvalidation = $lifecycle->sessionInvalidation(
            AuthenticationLifecycleSignal::ZahirUnavailable,
            $globalAccount,
            'logres',
            $occurredAt,
        );

        $this->assertSame('provider_failed', $providerOutcome?->value);
        $this->assertSame('zahir_unavailable', $unavailableOutcome?->value);
        $this->assertSame('provider_revoked', $providerInvalidation?->reason->value);
        $this->assertNull($unavailableInvalidation);
    }

    /** @return array<string, mixed> */
    private function fixtures(): array
    {
        return json_decode(
            file_get_contents(dirname(__DIR__, 2).'/contracts/v1/authentication-lifecycle-fixtures.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
