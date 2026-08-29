<?php

namespace App\Identity\WorkOs;

use App\Identity\VerifiedExternal;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class WorkOsVerifiedExternalAdapter
{
    public const PROVIDER = 'workos';

    public function __construct(private string $expectedIssuer, private string $expectedAudience) {}

    /** @param array<string, mixed> $claims */
    public function fromVerifiedClaims(array $claims): VerifiedExternal
    {
        foreach (['sub', 'iss', 'aud', 'iat'] as $required) {
            if (! isset($claims[$required])) {
                throw new InvalidArgumentException("Missing verified WorkOS claim [{$required}].");
            }
        }

        if ($claims['iss'] !== $this->expectedIssuer || ! $this->hasAudience($claims['aud'])) {
            throw new InvalidArgumentException('Verified WorkOS assertion has an unexpected issuer or audience.');
        }

        $safeClaims = array_filter([
            'email' => is_string($claims['email'] ?? null) ? $claims['email'] : null,
            'email_verified' => is_bool($claims['email_verified'] ?? null) ? $claims['email_verified'] : null,
            'name' => is_string($claims['name'] ?? null) ? $claims['name'] : null,
        ], static fn (mixed $value): bool => $value !== null);
        $assertedAt = CarbonImmutable::createFromTimestampUTC((int) $claims['iat']);

        return new VerifiedExternal(
            provider: self::PROVIDER,
            providerSubject: (string) $claims['sub'],
            claims: $safeClaims,
            provenance: array_filter([
                'issuer' => $this->expectedIssuer,
                'audience' => $this->expectedAudience,
                'asserted_at' => $assertedAt->toIso8601String(),
                'assertion_id' => is_string($claims['jti'] ?? null) ? $claims['jti'] : null,
            ], static fn (mixed $value): bool => $value !== null),
            authenticatedAt: $assertedAt,
        );
    }

    private function hasAudience(mixed $audience): bool
    {
        return $audience === $this->expectedAudience
            || (is_array($audience) && in_array($this->expectedAudience, $audience, true));
    }
}
