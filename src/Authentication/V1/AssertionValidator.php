<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

final readonly class AssertionValidator
{
    public function __construct(
        private AssertionDecoder $decoder,
        private SigningKeyResolver $keys,
        private SignatureVerifier $signatures,
    ) {}

    /**
     * Validates a Zahir-issued product authentication assertion. It does not
     * accept an external provider token or SDK object.
     *
     * @throws AuthenticationFailure
     */
    public function validate(string $compactAssertion, AssertionValidationPolicy $policy): AssertionClaims
    {
        try {
            $decoded = $this->decoder->decode($compactAssertion);
        } catch (AuthenticationFailure $failure) {
            throw $failure;
        } catch (\Throwable) {
            throw new AuthenticationFailure(FailureCode::MalformedAssertion);
        }

        if (! in_array($decoded->claims->issuer, $policy->allowedIssuers, true)) {
            throw new AuthenticationFailure(FailureCode::UnknownIssuer);
        }

        if (! in_array($decoded->algorithm, $policy->allowedAlgorithms, true)) {
            throw new AuthenticationFailure(FailureCode::DisallowedAlgorithm);
        }

        $key = $this->keys->resolve($decoded->claims->issuer, $decoded->keyId);

        if ($key === null) {
            $this->keys->refresh($decoded->claims->issuer);
            $key = $this->keys->resolve($decoded->claims->issuer, $decoded->keyId);
        }

        if ($key === null) {
            throw new AuthenticationFailure(FailureCode::UnknownSigningKey);
        }

        if (! $this->signatures->verify($compactAssertion, $decoded, $key)) {
            throw new AuthenticationFailure(FailureCode::InvalidSignature);
        }

        if (! in_array($policy->expectedAudience, $decoded->claims->audiences, true)) {
            throw new AuthenticationFailure(FailureCode::WrongAudience);
        }

        $earliestAcceptedExpiry = $policy->now->modify("-{$policy->clockToleranceSeconds} seconds");
        if ($decoded->claims->expiresAt <= $earliestAcceptedExpiry) {
            throw new AuthenticationFailure(FailureCode::ExpiredToken);
        }

        $latestAcceptedIssueTime = $policy->now->modify("+{$policy->clockToleranceSeconds} seconds");
        if ($decoded->claims->issuedAt > $latestAcceptedIssueTime) {
            throw new AuthenticationFailure(FailureCode::TokenNotYetValid);
        }

        $assertionLifetime = $decoded->claims->expiresAt->getTimestamp()
            - $decoded->claims->issuedAt->getTimestamp();
        if ($assertionLifetime > $policy->maxAssertionLifetimeSeconds) {
            throw new AuthenticationFailure(FailureCode::AssertionLifetimeExceeded);
        }

        if (! hash_equals($policy->expectedNonce, $decoded->claims->nonce)) {
            throw new AuthenticationFailure(FailureCode::NonceMismatch);
        }

        return $decoded->claims;
    }
}
