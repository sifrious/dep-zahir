<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycle;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleSignal;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;
use Sifrious\Zahir\Authentication\V1\InvalidAuthenticationLifecycleTransition;

final class AuthenticationLifecycleTest extends TestCase
{
    public function test_fixture_backed_lifecycle_transitions_are_explicit_and_deterministic(): void
    {
        $lifecycle = new AuthenticationLifecycle;

        foreach ($this->fixtures()['cases'] as $name => $case) {
            $transition = $lifecycle->transition(
                AuthenticationLifecycleState::from($case['from']),
                AuthenticationLifecycleSignal::from($case['signal']),
            );

            $this->assertSame($case['expected']['to'], $transition->to->value, $name);
            $this->assertSame(
                $case['expected']['invalidates_local_session'],
                $transition->invalidatesLocalSession,
                $name,
            );
            $this->assertSame($case['expected']['next_action'], $transition->nextAction->value, $name);
            $this->assertSame($case['expected']['login_outcome'], $transition->to->loginOutcome()?->value, $name);
            $this->assertSame($case['expected']['replayed'], $transition->replayed, $name);
        }
    }

    public function test_provider_revocation_and_zahir_unavailability_remain_distinct(): void
    {
        $lifecycle = new AuthenticationLifecycle;
        $providerRevoked = $lifecycle->transition(
            AuthenticationLifecycleState::Authenticated,
            AuthenticationLifecycleSignal::ProviderRevoked,
        );
        $zahirUnavailable = $lifecycle->transition(
            AuthenticationLifecycleState::Authenticated,
            AuthenticationLifecycleSignal::ZahirUnavailable,
        );

        $this->assertNotSame($providerRevoked->to, $zahirUnavailable->to);
        $this->assertNotSame($providerRevoked->nextAction, $zahirUnavailable->nextAction);
        $this->assertTrue($providerRevoked->invalidatesLocalSession);
        $this->assertFalse($zahirUnavailable->invalidatesLocalSession);
    }

    public function test_recovery_cannot_bypass_provider_owned_verification(): void
    {
        $this->expectException(InvalidAuthenticationLifecycleTransition::class);

        (new AuthenticationLifecycle)->transition(
            AuthenticationLifecycleState::ProviderRevoked,
            AuthenticationLifecycleSignal::RecoveryAccepted,
        );
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
