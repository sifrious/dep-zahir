<?php

namespace App\Identity;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class VerifiedExternal
{
    /**
     * @param  array{email?: string, email_verified?: bool, name?: string}  $claims
     * @param  array{issuer: string, audience: string, asserted_at: string, assertion_id?: string}  $provenance
     */
    public function __construct(
        public string $provider,
        public string $providerSubject,
        public array $claims,
        public array $provenance,
        public CarbonImmutable $authenticatedAt,
    ) {
        if ($provider === '' || $providerSubject === '') {
            throw new InvalidArgumentException('Provider and provider subject are required.');
        }

        foreach (array_keys($claims) as $claim) {
            if (! in_array($claim, ['email', 'email_verified', 'name'], true)) {
                throw new InvalidArgumentException("Unsafe external claim [{$claim}].");
            }
        }

        foreach (['issuer', 'audience', 'asserted_at'] as $field) {
            if (! isset($provenance[$field]) || ! is_string($provenance[$field]) || $provenance[$field] === '') {
                throw new InvalidArgumentException("Provenance field [{$field}] is required.");
            }
        }
    }
}
