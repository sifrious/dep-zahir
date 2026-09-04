<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

enum LoginOutcomeType: string
{
    case Authenticated = 'authenticated';
    case UnauthorizedProduct = 'unauthorized_product';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case ProviderFailed = 'provider_failed';
    case ZahirUnavailable = 'zahir_unavailable';
}

enum FailureCategory: string
{
    case Protocol = 'protocol';
    case Provider = 'provider';
    case Account = 'account';
    case Entitlement = 'entitlement';
    case Availability = 'availability';
    case Authorization = 'authorization';
}

enum FailureCode: string
{
    case Canceled = 'canceled';
    case StateMismatch = 'state_mismatch';
    case NonceMismatch = 'nonce_mismatch';
    case WrongAudience = 'wrong_audience';
    case UnknownIssuer = 'unknown_issuer';
    case ExpiredToken = 'expired_token';
    case TokenNotYetValid = 'token_not_yet_valid';
    case UnknownSigningKey = 'unknown_signing_key';
    case DisallowedAlgorithm = 'disallowed_algorithm';
    case InvalidSignature = 'invalid_signature';
    case MalformedAssertion = 'malformed_assertion';
    case ReplayedAssertion = 'replayed_assertion';
    case ProviderFailed = 'provider_failed';
    case ZahirUnavailable = 'zahir_unavailable';
    case AccountSuspended = 'account_suspended';
    case ProductEntitlementRevoked = 'product_entitlement_revoked';
    case ProductAccessDenied = 'product_access_denied';
    case ExecutionNotAuthorized = 'execution_not_authorized';

    public function category(): FailureCategory
    {
        return match ($this) {
            self::ProviderFailed => FailureCategory::Provider,
            self::ZahirUnavailable => FailureCategory::Availability,
            self::AccountSuspended => FailureCategory::Account,
            self::ProductEntitlementRevoked, self::ProductAccessDenied => FailureCategory::Entitlement,
            self::ExecutionNotAuthorized => FailureCategory::Authorization,
            default => FailureCategory::Protocol,
        };
    }

    public function retryable(): bool
    {
        return in_array($this, [self::ProviderFailed, self::ZahirUnavailable], true);
    }
}

final class AuthenticationFailure extends RuntimeException
{
    public function __construct(public readonly FailureCode $failureCode)
    {
        parent::__construct($failureCode->value);
    }

    public function category(): FailureCategory
    {
        return $this->failureCode->category();
    }

    public function retryable(): bool
    {
        return $this->failureCode->retryable();
    }
}

final readonly class GlobalAccountIdentity
{
    public function __construct(public string $accountId)
    {
        if (! str_starts_with($accountId, 'acc_')) {
            throw new InvalidArgumentException('A Zahir global account ID must begin with acc_.');
        }
    }
}

final readonly class ProductAccountProjection
{
    public function __construct(
        public string $product,
        public string $localUserId,
        public GlobalAccountIdentity $globalAccount,
    ) {
        if ($product === '' || $localUserId === '') {
            throw new InvalidArgumentException('Product and local user ID are required.');
        }
    }
}

final readonly class ProductSessionIdentity
{
    public function __construct(
        public string $localSessionId,
        public ProductAccountProjection $account,
    ) {
        if ($localSessionId === '') {
            throw new InvalidArgumentException('A product-local session ID is required.');
        }
    }
}

final readonly class ExternalProviderConnection
{
    public function __construct(public string $provider, public string $providerSubject)
    {
        if ($provider === '' || $providerSubject === '') {
            throw new InvalidArgumentException('Provider and provider subject are required.');
        }
    }
}

final readonly class RunnerEnrollmentIdentity
{
    public function __construct(
        public string $enrollmentId,
        public string $runnerId,
        public GlobalAccountIdentity $enrolledBy,
    ) {
        if ($enrollmentId === '' || $runnerId === '') {
            throw new InvalidArgumentException('Runner enrollment and runner IDs are required.');
        }
    }
}

final readonly class ExecutionAuthorization
{
    public function __construct(
        public bool $allowed,
        public ?string $authorizationId,
        public FailureCode $reason = FailureCode::ExecutionNotAuthorized,
    ) {
        if ($allowed && ($authorizationId === null || $authorizationId === '')) {
            throw new InvalidArgumentException('Allowed execution requires an authorization ID.');
        }
    }
}

final readonly class RepositoryGrant
{
    /** @param list<string> $scopes */
    public function __construct(public string $repositoryId, public array $scopes)
    {
        if ($repositoryId === '' || $scopes === []) {
            throw new InvalidArgumentException('Repository ID and at least one scope are required.');
        }
    }
}

final readonly class WorkspaceGrant
{
    /** @param list<string> $scopes */
    public function __construct(public string $workspaceId, public array $scopes)
    {
        if ($workspaceId === '' || $scopes === []) {
            throw new InvalidArgumentException('Workspace ID and at least one scope are required.');
        }
    }
}

final readonly class ProductEntitlement
{
    public function __construct(
        public string $product,
        public string $entitlement,
        public string $grantId,
    ) {
        if ($product === '' || $entitlement === '' || $grantId === '') {
            throw new InvalidArgumentException('Product, entitlement, and grant ID are required.');
        }
    }
}

enum GlobalAccountStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}

final readonly class GlobalAccountResolution
{
    public function __construct(
        public GlobalAccountIdentity $account,
        public GlobalAccountStatus $status,
        public bool $created,
    ) {}
}

enum ProductEntitlementStatus: string
{
    case Active = 'active';
    case Absent = 'absent';
    case Revoked = 'revoked';
    case Expired = 'expired';
    case ProductInactive = 'product_inactive';
}

final readonly class ProductEntitlementDecision
{
    public function __construct(
        public GlobalAccountIdentity $account,
        public string $product,
        public string $entitlement,
        public ProductEntitlementStatus $status,
        public ?string $grantId,
    ) {
        if ($product === '' || $entitlement === '') {
            throw new InvalidArgumentException('Product and entitlement are required.');
        }

        if ($status === ProductEntitlementStatus::Active && ($grantId === null || $grantId === '')) {
            throw new InvalidArgumentException('An active entitlement requires a grant ID.');
        }

        if ($status !== ProductEntitlementStatus::Active && $grantId !== null) {
            throw new InvalidArgumentException('A denied entitlement cannot expose a grant ID.');
        }
    }

    public function allowed(): bool
    {
        return $this->status === ProductEntitlementStatus::Active;
    }
}

final readonly class AssertionClaims
{
    /**
     * The subject is always the opaque Zahir global account ID. External provider
     * subjects are represented separately by ExternalProviderConnection.
     *
     * @param  list<string>  $audiences
     */
    public function __construct(
        public string $issuer,
        public array $audiences,
        public string $subject,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
        public string $nonce,
        public string $assertionId,
    ) {
        if ($issuer === '' || $audiences === [] || $nonce === '' || $assertionId === '') {
            throw new InvalidArgumentException('Issuer, audience, nonce, and assertion ID are required.');
        }

        if (! str_starts_with($subject, 'acc_')) {
            throw new InvalidArgumentException('Assertion subject must be an opaque Zahir account ID.');
        }

        if ($expiresAt <= $issuedAt) {
            throw new InvalidArgumentException('Assertion expiry must be after its issue time.');
        }
    }
}

final readonly class VerifiedCallbackResult
{
    public function __construct(
        public AssertionClaims $claims,
        public ExternalProviderConnection $externalConnection,
    ) {}
}

interface GlobalAccountResolver
{
    public function resolve(VerifiedCallbackResult $callback): GlobalAccountResolution;
}

interface ProductEntitlementReader
{
    public function read(
        GlobalAccountIdentity $account,
        string $product,
        string $entitlement,
    ): ProductEntitlementDecision;
}

interface VerifiedCallbackConsumer
{
    /**
     * Resolves the global account, evaluates product access, and returns one of
     * the explicit login outcomes without creating product-domain authorization.
     */
    public function consume(VerifiedCallbackResult $callback): LoginOutcome;
}

final readonly class DecodedAssertion
{
    public function __construct(
        public string $keyId,
        public string $algorithm,
        public AssertionClaims $claims,
    ) {
        if ($keyId === '' || $algorithm === '') {
            throw new InvalidArgumentException('Signing key ID and algorithm are required.');
        }
    }
}

final readonly class SigningKey
{
    public function __construct(public string $keyId, public mixed $material)
    {
        if ($keyId === '') {
            throw new InvalidArgumentException('Signing key ID is required.');
        }
    }
}

interface AssertionDecoder
{
    /**
     * Decode only. Implementations must not treat decoded data as trusted until
     * AssertionValidator invokes SignatureVerifier successfully.
     */
    public function decode(string $compactAssertion): DecodedAssertion;
}

interface SigningKeyResolver
{
    public function resolve(string $issuer, string $keyId): ?SigningKey;

    /**
     * Refresh cached keys once on an unknown key ID. Implementations must enforce
     * an issuer allowlist and normal HTTP cache rules.
     */
    public function refresh(string $issuer): void;
}

interface SignatureVerifier
{
    public function verify(string $compactAssertion, DecodedAssertion $decoded, SigningKey $key): bool;
}

final readonly class AssertionValidationPolicy
{
    /**
     * @param  list<string>  $allowedIssuers
     * @param  list<string>  $allowedAlgorithms
     */
    public function __construct(
        public array $allowedIssuers,
        public string $expectedAudience,
        public string $expectedNonce,
        public DateTimeImmutable $now,
        public array $allowedAlgorithms = ['RS256'],
        public int $clockToleranceSeconds = 60,
    ) {
        if ($allowedIssuers === [] || $expectedAudience === '' || $expectedNonce === '') {
            throw new InvalidArgumentException('Issuer allowlist, audience, and nonce are required.');
        }

        if ($clockToleranceSeconds < 0 || $clockToleranceSeconds > 300) {
            throw new InvalidArgumentException('Clock tolerance must be between zero and 300 seconds.');
        }
    }
}

final readonly class AuthenticatedLogin
{
    public function __construct(
        public GlobalAccountIdentity $globalAccount,
        public ExternalProviderConnection $externalConnection,
        public ProductEntitlement $productEntitlement,
        public AssertionClaims $claims,
    ) {
        if ($globalAccount->accountId !== $claims->subject) {
            throw new InvalidArgumentException('Assertion subject does not map to the resolved global account.');
        }
    }
}

final readonly class LoginOutcome
{
    private function __construct(
        public LoginOutcomeType $type,
        public ?AuthenticatedLogin $login,
        public ?FailureCode $failure,
    ) {}

    public static function authenticated(AuthenticatedLogin $login): self
    {
        return new self(LoginOutcomeType::Authenticated, $login, null);
    }

    public static function failed(LoginOutcomeType $type, FailureCode $failure): self
    {
        if ($type === LoginOutcomeType::Authenticated) {
            throw new InvalidArgumentException('An authenticated outcome requires an authenticated login.');
        }

        return new self($type, null, $failure);
    }
}

final readonly class StartAuthentication
{
    public function __construct(public string $product, public string $returnUri)
    {
        if ($product === '' || $returnUri === '') {
            throw new InvalidArgumentException('Product and return URI are required.');
        }
    }
}

final readonly class PendingAuthentication
{
    public function __construct(
        public string $state,
        public string $nonce,
        public DateTimeImmutable $expiresAt,
        public string $authorizationUri,
    ) {
        if ($state === '' || $nonce === '' || $authorizationUri === '') {
            throw new InvalidArgumentException('State, nonce, and authorization URI are required.');
        }
    }
}

final readonly class AuthenticationCallback
{
    public function __construct(public string $state, public ?string $code, public ?string $error = null)
    {
        if ($state === '' || ($code === null && $error === null)) {
            throw new InvalidArgumentException('Callback state and either code or error are required.');
        }
    }
}

final readonly class LogoutRequest
{
    public function __construct(
        public GlobalAccountIdentity $globalAccount,
        public string $product,
        public string $localSessionId,
        public string $postLogoutUri,
    ) {
        if ($product === '' || $localSessionId === '' || $postLogoutUri === '') {
            throw new InvalidArgumentException('Product, local session, and post-logout URI are required.');
        }
    }
}

final readonly class LogoutOutcome
{
    public function __construct(
        public bool $localSessionInvalidated,
        public bool $globalLogoutRequested,
        public ?string $redirectUri,
    ) {}
}

enum SessionInvalidationReason: string
{
    case Logout = 'logout';
    case AccountSuspended = 'account_suspended';
    case EntitlementRevoked = 'entitlement_revoked';
    case ZahirInvalidated = 'zahir_invalidated';
}

final readonly class SessionInvalidation
{
    public function __construct(
        public GlobalAccountIdentity $globalAccount,
        public string $product,
        public SessionInvalidationReason $reason,
        public DateTimeImmutable $occurredAt,
    ) {
        if ($product === '') {
            throw new InvalidArgumentException('Product is required for session invalidation.');
        }
    }
}

interface SessionInvalidationConsumer
{
    /**
     * Returns the number of product-local sessions invalidated. Implementations
     * must be idempotent for repeated Zahir events.
     */
    public function invalidate(SessionInvalidation $invalidation): int;
}

interface AuthenticationConsumer
{
    public function begin(StartAuthentication $request): PendingAuthentication;

    /**
     * Implementations consume state once and reject expired/replayed attempts
     * before exchanging the opaque callback code.
     */
    public function complete(AuthenticationCallback $callback, PendingAuthentication $pending): LoginOutcome;

    /**
     * Product-local invalidation is mandatory. Global/provider logout is best
     * effort and must never restore or prolong the local session.
     */
    public function logout(LogoutRequest $request): LogoutOutcome;
}
