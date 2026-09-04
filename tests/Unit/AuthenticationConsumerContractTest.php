<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sifrious\Zahir\Authentication\V1\AssertionClaims;
use Sifrious\Zahir\Authentication\V1\AssertionDecoder;
use Sifrious\Zahir\Authentication\V1\AssertionValidationPolicy;
use Sifrious\Zahir\Authentication\V1\AssertionValidator;
use Sifrious\Zahir\Authentication\V1\AuthenticatedLogin;
use Sifrious\Zahir\Authentication\V1\AuthenticationCallback;
use Sifrious\Zahir\Authentication\V1\AuthenticationFailure;
use Sifrious\Zahir\Authentication\V1\DecodedAssertion;
use Sifrious\Zahir\Authentication\V1\ExecutionAuthorization;
use Sifrious\Zahir\Authentication\V1\ExternalProviderConnection;
use Sifrious\Zahir\Authentication\V1\FailureCode;
use Sifrious\Zahir\Authentication\V1\GlobalAccountIdentity;
use Sifrious\Zahir\Authentication\V1\GlobalAccountResolution;
use Sifrious\Zahir\Authentication\V1\GlobalAccountResolver;
use Sifrious\Zahir\Authentication\V1\GlobalAccountStatus;
use Sifrious\Zahir\Authentication\V1\LoginOutcome;
use Sifrious\Zahir\Authentication\V1\LoginOutcomeType;
use Sifrious\Zahir\Authentication\V1\ProductAccountProjection;
use Sifrious\Zahir\Authentication\V1\ProductEntitlement;
use Sifrious\Zahir\Authentication\V1\ProductEntitlementDecision;
use Sifrious\Zahir\Authentication\V1\ProductEntitlementReader;
use Sifrious\Zahir\Authentication\V1\ProductEntitlementStatus;
use Sifrious\Zahir\Authentication\V1\ProductSessionIdentity;
use Sifrious\Zahir\Authentication\V1\SessionInvalidation;
use Sifrious\Zahir\Authentication\V1\SessionInvalidationConsumer;
use Sifrious\Zahir\Authentication\V1\SessionInvalidationReason;
use Sifrious\Zahir\Authentication\V1\SignatureVerifier;
use Sifrious\Zahir\Authentication\V1\SigningKey;
use Sifrious\Zahir\Authentication\V1\SigningKeyResolver;
use Sifrious\Zahir\Authentication\V1\VerifiedCallbackResult;

final class AuthenticationConsumerContractTest extends TestCase
{
    public function test_successful_login_fixture_validates(): void
    {
        [$claims, $resolver] = $this->validateFixture('successful_login');

        $this->assertSame('acc_01FIXTUREACCOUNT', $claims->subject);
        $this->assertSame(0, $resolver->refreshes);
    }

    #[DataProvider('invalidAssertionCases')]
    public function test_invalid_assertion_fixtures_fail_closed(string $caseName, FailureCode $expected): void
    {
        try {
            $this->validateFixture($caseName);
            $this->fail("Fixture [{$caseName}] did not fail.");
        } catch (AuthenticationFailure $failure) {
            $this->assertSame($expected, $failure->failureCode);
        }
    }

    /** @return iterable<string, array{string, FailureCode}> */
    public static function invalidAssertionCases(): iterable
    {
        yield 'wrong audience' => ['wrong_audience', FailureCode::WrongAudience];
        yield 'unknown issuer' => ['unknown_issuer', FailureCode::UnknownIssuer];
        yield 'expired token' => ['expired_token', FailureCode::ExpiredToken];
        yield 'excessive assertion lifetime' => [
            'assertion_lifetime_exceeded',
            FailureCode::AssertionLifetimeExceeded,
        ];
        yield 'nonce mismatch' => ['nonce_mismatch', FailureCode::NonceMismatch];
        yield 'unknown signing key' => ['unknown_signing_key', FailureCode::UnknownSigningKey];
    }

    #[DataProvider('invalidCallbackCases')]
    public function test_callback_requires_exactly_one_non_empty_code_or_error(
        ?string $code,
        ?string $error,
    ): void {
        $this->expectException(InvalidArgumentException::class);

        new AuthenticationCallback('state-fixture', $code, $error);
    }

    /** @return iterable<string, array{?string, ?string}> */
    public static function invalidCallbackCases(): iterable
    {
        yield 'neither' => [null, null];
        yield 'empty code' => ['', null];
        yield 'empty error' => [null, ''];
        yield 'mixed' => ['code-fixture', 'access_denied'];
    }

    public function test_unknown_key_refresh_supports_rotation_once(): void
    {
        [$claims, $resolver] = $this->validateFixture('rotated_key');

        $this->assertSame('assertion-rotated', $claims->assertionId);
        $this->assertSame(1, $resolver->refreshes);
    }

    public function test_revoked_entitlement_is_an_explicit_login_outcome(): void
    {
        $outcome = LoginOutcome::failed(
            LoginOutcomeType::UnauthorizedProduct,
            FailureCode::ProductEntitlementRevoked,
        );

        $this->assertSame(
            $this->fixtures()['cases']['revoked_product_entitlement']['expected']['outcome'],
            $outcome->type->value,
        );
        $this->assertSame(FailureCode::ProductEntitlementRevoked, $outcome->failure);
    }

    public function test_login_does_not_imply_execution_repository_or_workspace_authorization(): void
    {
        [$claims] = $this->validateFixture('authenticated_without_execution_authorization');
        $fixture = $this->fixtures()['cases']['authenticated_without_execution_authorization'];
        $globalAccount = new GlobalAccountIdentity($claims->subject);
        $login = new AuthenticatedLogin(
            globalAccount: $globalAccount,
            externalConnection: new ExternalProviderConnection('fixture-provider', 'external-subject'),
            productEntitlement: new ProductEntitlement('burdgeon', 'access', 'grant-fixture'),
            claims: $claims,
        );
        $outcome = LoginOutcome::authenticated($login);
        $execution = new ExecutionAuthorization(
            allowed: $fixture['execution_authorization']['allowed'],
            authorizationId: $fixture['execution_authorization']['authorization_id'],
        );

        $this->assertSame(LoginOutcomeType::Authenticated, $outcome->type);
        $this->assertFalse($execution->allowed);
        $this->assertArrayNotHasKey('executionAuthorization', get_object_vars($login));
        $this->assertArrayNotHasKey('repositoryGrants', get_object_vars($login));
        $this->assertArrayNotHasKey('workspaceGrants', get_object_vars($login));
    }

    public function test_verified_callback_resolves_account_and_reads_entitlement_through_public_seams(): void
    {
        [$claims] = $this->validateFixture('successful_login');
        $callback = new VerifiedCallbackResult(
            claims: $claims,
            externalConnection: new ExternalProviderConnection('fixture-provider', 'external-subject'),
        );
        $resolver = new class implements GlobalAccountResolver
        {
            public function resolve(VerifiedCallbackResult $callback): GlobalAccountResolution
            {
                return new GlobalAccountResolution(
                    account: new GlobalAccountIdentity($callback->claims->subject),
                    status: GlobalAccountStatus::Active,
                    created: false,
                );
            }
        };
        $entitlements = new class implements ProductEntitlementReader
        {
            public function read(
                GlobalAccountIdentity $account,
                string $product,
                string $entitlement,
            ): ProductEntitlementDecision {
                return new ProductEntitlementDecision(
                    account: $account,
                    product: $product,
                    entitlement: $entitlement,
                    status: ProductEntitlementStatus::Active,
                    grantId: 'grant-fixture',
                );
            }
        };

        $resolution = $resolver->resolve($callback);
        $decision = $entitlements->read($resolution->account, 'burdgeon', 'access');

        $this->assertSame(GlobalAccountStatus::Active, $resolution->status);
        $this->assertTrue($decision->allowed());
        $this->assertSame($claims->subject, $decision->account->accountId);
    }

    public function test_minimal_projection_and_session_invalidation_remain_product_owned(): void
    {
        $projection = new ProductAccountProjection(
            product: 'burdgeon',
            localUserId: 'user-local',
            globalAccount: new GlobalAccountIdentity('acc_01FIXTUREACCOUNT'),
        );
        $session = new ProductSessionIdentity('session-local', $projection);
        $consumer = new class implements SessionInvalidationConsumer
        {
            public function invalidate(SessionInvalidation $invalidation): int
            {
                return 1;
            }
        };
        $invalidated = $consumer->invalidate(new SessionInvalidation(
            globalAccount: $projection->globalAccount,
            product: $projection->product,
            reason: SessionInvalidationReason::EntitlementRevoked,
            occurredAt: new DateTimeImmutable('2026-09-04T12:00:00Z'),
        ));

        $this->assertSame(['product', 'localUserId', 'globalAccount'], array_keys(get_object_vars($projection)));
        $this->assertSame($projection, $session->account);
        $this->assertSame(1, $invalidated);
    }

    /** @return array{AssertionClaims, FixtureSigningKeyResolver} */
    private function validateFixture(string $caseName): array
    {
        $fixture = $this->expandedCase($caseName);
        $assertion = $fixture['assertion'];
        $configuration = $this->fixtures()['configuration'];
        $decoded = new DecodedAssertion(
            keyId: $assertion['key_id'],
            algorithm: $assertion['algorithm'],
            claims: new AssertionClaims(
                issuer: $assertion['issuer'],
                audiences: $assertion['audiences'],
                subject: $assertion['subject'],
                issuedAt: new DateTimeImmutable($assertion['issued_at']),
                expiresAt: new DateTimeImmutable($assertion['expires_at']),
                nonce: $assertion['nonce'],
                assertionId: $assertion['assertion_id'],
            ),
        );
        $resolver = new FixtureSigningKeyResolver(
            initial: $fixture['keys']['initial'],
            afterRefresh: $fixture['keys']['after_refresh'],
        );
        $validator = new AssertionValidator(
            decoder: new FixtureAssertionDecoder($decoded),
            keys: $resolver,
            signatures: new AcceptingSignatureVerifier,
        );

        $claims = $validator->validate('fixture:'.$caseName, new AssertionValidationPolicy(
            allowedIssuers: $configuration['allowed_issuers'],
            expectedAudience: $configuration['expected_audience'],
            expectedNonce: $configuration['expected_nonce'],
            now: new DateTimeImmutable($configuration['now']),
            clockToleranceSeconds: $configuration['clock_tolerance_seconds'],
            maxAssertionLifetimeSeconds: $configuration['max_assertion_lifetime_seconds'],
        ));

        return [$claims, $resolver];
    }

    /** @return array<string, mixed> */
    private function expandedCase(string $caseName): array
    {
        $fixtures = $this->fixtures();
        $case = $fixtures['cases'][$caseName];

        if (! isset($case['from'])) {
            return $case;
        }

        $base = $fixtures['cases'][$case['from']];
        $base['assertion'] = array_replace($base['assertion'], $case['override'] ?? []);

        return array_replace($base, array_diff_key($case, ['from' => true, 'override' => true]));
    }

    /** @return array<string, mixed> */
    private function fixtures(): array
    {
        return json_decode(
            file_get_contents(dirname(__DIR__, 2).'/contracts/v1/authentication-consumer-fixtures.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}

final readonly class FixtureAssertionDecoder implements AssertionDecoder
{
    public function __construct(private DecodedAssertion $assertion) {}

    public function decode(string $compactAssertion): DecodedAssertion
    {
        return $this->assertion;
    }
}

final class FixtureSigningKeyResolver implements SigningKeyResolver
{
    public int $refreshes = 0;

    /** @param list<string> $initial @param list<string> $afterRefresh */
    public function __construct(private array $initial, private readonly array $afterRefresh) {}

    public function resolve(string $issuer, string $keyId): ?SigningKey
    {
        return in_array($keyId, $this->initial, true) ? new SigningKey($keyId, 'fixture-key') : null;
    }

    public function refresh(string $issuer): void
    {
        $this->refreshes++;
        $this->initial = $this->afterRefresh;
    }
}

final class AcceptingSignatureVerifier implements SignatureVerifier
{
    public function verify(string $compactAssertion, DecodedAssertion $decoded, SigningKey $key): bool
    {
        return true;
    }
}
