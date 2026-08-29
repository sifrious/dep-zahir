<?php

namespace Tests\Unit;

use App\Identity\WorkOs\WorkOsVerifiedExternalAdapter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WorkOsVerifiedExternalAdapterTest extends TestCase
{
    public function test_it_maps_verified_claims_to_provider_neutral_contract(): void
    {
        $external = (new WorkOsVerifiedExternalAdapter('https://api.workos.com/', 'client_123'))
            ->fromVerifiedClaims([
                'sub' => 'user_123', 'iss' => 'https://api.workos.com/', 'aud' => 'client_123',
                'iat' => 1788004800, 'email' => 'person@example.test', 'email_verified' => true,
                'name' => 'Person', 'organization_id' => 'ignored',
            ]);

        $this->assertSame('workos', $external->provider);
        $this->assertSame('user_123', $external->providerSubject);
        $this->assertArrayNotHasKey('organization_id', $external->claims);
    }

    public function test_it_rejects_unexpected_issuer_or_audience(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new WorkOsVerifiedExternalAdapter('https://api.workos.com/', 'client_123'))
            ->fromVerifiedClaims(['sub' => 'user_123', 'iss' => 'wrong', 'aud' => 'client_123', 'iat' => 1788004800]);
    }
}
