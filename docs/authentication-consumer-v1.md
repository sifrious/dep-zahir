# Zahir authentication consumer contract v1

This contract is the provider-neutral seam between Zahir and a product such as
Burdgeon. Its PHP API is `Sifrious\Zahir\Authentication\V1`; the deterministic
fixtures are `contracts/v1/authentication-consumer-fixtures.json`.

The contract is versioned independently of deployment configuration. Adding a
real issuer, callback URL, key source, or redacted token verification record
later does not change v1.

## Boundaries that must not collapse

| Concept | Stable key | Owner | Meaning |
| --- | --- | --- | --- |
| Zahir person/global account | opaque `acc_…` | Zahir | Portfolio-wide person/account identity |
| Product-local user/session | product user ID + session ID, projected by `acc_…` | Product | Application login state and local user |
| External provider connection | `(provider, provider_subject)` | Zahir; credentials remain provider-owned | Connection used to resolve a global account |
| Runner enrollment | enrollment ID + runner ID | Runner/product domain | Who enrolled a runner; not a login session |
| Execution authorization | authorization/decision ID | Product execution domain | Permission for one defined execution operation |
| Repository grant | repository ID + explicit scopes | Product authorization domain | Repository capabilities |
| Workspace grant | workspace ID + explicit scopes | Product authorization domain | Workspace capabilities |

`AuthenticatedLogin` contains only the global account, external connection,
product entitlement, and verified assertion claims. It intentionally cannot
contain runner enrollment, execution authorization, repository grants, or
workspace grants. A successful login therefore proves none of those things.
Products must make separate fail-closed authorization decisions.

Email and name are mutable profile metadata. They are never subjects, identity
keys, or account-linking inputs.

## Frozen Wave 1 vocabulary

The following names are the v1 public seam. Dependent work uses these types
directly and must not introduce parallel account, identity, entitlement, or
session-outcome objects. Changes within v1 are additive and backward compatible.

| Concern | Frozen PHP names | Stable value or key |
| --- | --- | --- |
| Begin authentication | `AuthenticationConsumer::begin(StartAuthentication): PendingAuthentication` | product, return URI, state, nonce, expiry, authorization URI |
| Callback input/result | `AuthenticationCallback`, `AuthenticationConsumer::complete()`, `VerifiedCallbackResult`, `VerifiedCallbackConsumer::consume()` | verified claims plus provider-neutral external connection |
| Global account | `GlobalAccountIdentity`, `GlobalAccountResolver`, `GlobalAccountResolution`, `GlobalAccountStatus` | `GlobalAccountIdentity::$accountId` (`acc_…`); status `active` or `suspended` |
| Product account projection | `ProductAccountProjection` | unique product-local projection keyed by `ProductAccountProjection::$globalAccount->accountId` |
| Product-local session | `ProductSessionIdentity` | local session ID referencing one product projection |
| Entitlement read | `ProductEntitlementReader::read()`, `ProductEntitlementDecision`, `ProductEntitlementStatus` | `active`, `absent`, `revoked`, `expired`, `product_inactive` |
| Logout | `AuthenticationConsumer::logout(LogoutRequest): LogoutOutcome` | mandatory local invalidation; optional global logout request/redirect |
| Session invalidation | `SessionInvalidation`, `SessionInvalidationReason`, `SessionInvalidationConsumer::invalidate()` | `logout`, `account_suspended`, `entitlement_revoked`, `zahir_invalidated` |
| Login outcome | `LoginOutcome`, `LoginOutcomeType` | `authenticated`, `unauthorized_product`, `canceled`, `expired`, `suspended`, `provider_failed`, `zahir_unavailable` |

`GlobalAccountIdentity` is the sole public global-account identity object.
Provider revocation, session expiry, recovery, and delivery-ledger state do not
create alternate account identifiers or broaden `GlobalAccountStatus`; they
compose around the account ID and typed outcomes above.

## Public PHP seams

- `AuthenticationConsumer` begins authentication, completes the browser
  callback into a typed login outcome, and logs out.
- `VerifiedCallbackResult` carries only verified Zahir claims and the
  provider-neutral external connection; no provider credential or SDK type.
- `GlobalAccountResolver` maps that result to an opaque account and explicit
  active/suspended status.
- `ProductEntitlementReader` returns an active, absent, revoked, expired, or
  inactive-product decision. Only an active decision may expose a grant ID.
- `VerifiedCallbackConsumer` composes callback verification, account
  resolution, and entitlement evaluation into the explicit login outcomes.
- `SessionInvalidationConsumer` idempotently invalidates product-local sessions
  for logout, account suspension, entitlement revocation, or Zahir invalidation.

These are transport- and storage-independent interfaces. A consumer receives
values and decisions, never Eloquent models, table identifiers, database
connections, or Logres-specific classes.

## Start, callback, and session lifecycle

`AuthenticationConsumer::begin()` accepts a product key and allowlisted return
URI. It returns an authorization URI plus cryptographically random state and
nonce values with a short expiry. The product stores state and nonce in a
server-side, single-use pending-login record. Browser cookies contain only an
opaque reference and use Secure, HttpOnly, and appropriate SameSite settings.

`AuthenticationConsumer::complete()` validates and atomically consumes the
pending record before creating a product session:

1. Reject missing, mismatched, expired, or previously consumed state.
2. Treat `access_denied` as cancellation; classify other upstream errors as
   provider failures.
3. Exchange an opaque callback code once through Zahir.
4. Verify the Zahir-issued assertion using the rules below.
5. Resolve `sub` to a local product projection keyed by the same `acc_…`.
6. Require an active account and active product entitlement.
7. Rotate the product-local session ID and persist only the minimum projection.

The minimum `ProductAccountProjection` is the Zahir account ID plus the
product-owned local user ID and product key. `ProductSessionIdentity` references
that projection separately. Provider subjects, provider credentials, raw
assertions, authorization codes, and signing keys are not product account
columns.

`AuthenticationConsumer::logout()` always invalidates the product-local
session first. Zahir/global/provider logout is optional best effort. Products
also invalidate matching local sessions when Zahir reports account suspension,
entitlement revocation, or session invalidation. A failed global logout must
never restore or extend a local session. Post-logout redirects use an exact
allowlist.

## Required assertion fields

The protected header requires `kid` and an explicitly allowed asymmetric
algorithm. The v1 claim set requires:

- `iss`: exact member of a configured Zahir issuer allowlist.
- `aud`: string or array containing the configured product audience.
- `sub`: opaque Zahir global account ID (`acc_…`), never a provider subject.
- `iat`: issue time.
- `exp`: expiry after `iat`.
- `nonce`: exact, constant-time match with the consumed pending login.
- `jti`: unique assertion ID used by consumers to reject replay.

The external provider connection is a separate provider-neutral value containing
only `provider` and `provider_subject`. WorkOS tokens, users, sessions, callback
types, SDK objects, and JWKS types stay behind Zahir's integration adapter.

Consumers reject assertions with missing or malformed required fields. They
must cap clock tolerance at five minutes; the contract default is 60 seconds.
Assertions are not refresh tokens and must have a short deployment-configured
lifetime. Product sessions have their own idle and absolute expiries and may
never outlive a suspension, revocation, or invalidation event known to the
product.

## Issuer, audience, signature, and key rotation

`AssertionValidator` applies the following fail-closed order:

1. Decode without trusting the result.
2. Match `iss` against a static allowlist. Never fetch keys for an unknown
   issuer.
3. Require an allowed algorithm; never honor an assertion-selected algorithm.
4. Resolve `kid` from the configured issuer key cache.
5. On an unknown `kid`, refresh that issuer's keys once and resolve again.
6. Reject a still-unknown key, then verify the signature.
7. Validate audience, expiry, issue time, and nonce.
8. Atomically record `jti` as consumed before creating the local session.

The key resolver follows cache headers, retains still-valid previous keys during
an overlap window, bounds stale use, and fails closed when refresh cannot
produce the requested key. Rotation is represented by the resolver interface,
so a future live key URL or captured verification evidence is configuration and
test-adapter data rather than a public-contract change.

The package deliberately does not provide a WorkOS URL, callback URL, token, or
JWKS document. Deterministic fixtures use URNs and abstract key IDs, not claims
of live provider behavior.

## Outcomes and failure classification

Login outcomes are `authenticated`, `unauthorized_product`, `canceled`,
`expired`, `suspended`, `provider_failed`, and `zahir_unavailable`.

Failures are stable machine-readable codes grouped as:

- `protocol`: state/nonce mismatch, wrong audience, unknown issuer, expiry,
  future issue time, unknown key, disallowed algorithm, invalid signature,
  malformed assertion, or replay.
- `provider`: cancellation is a user outcome; other provider failures are
  separately classified and may be retryable.
- `account`: suspended global account.
- `entitlement`: denied or revoked product access.
- `availability`: Zahir unavailable; retryable without treating the user as
  authenticated.
- `authorization`: execution not authorized. This is not a login failure and
  must remain a separate product-domain decision.

Protocol, account, entitlement, and authorization failures are not retried as
availability errors. User-facing messages must not disclose whether another
account owns an external identity or reveal signing internals.

## Authentication lifecycle transitions

`AuthenticationLifecycle` maps lifecycle signals onto the frozen
`LoginOutcomeType` values and the existing `SessionInvalidation` contract. It
does not define another identity or outcome hierarchy:

- logout and session expiry emit session invalidations for the sole
  `GlobalAccountIdentity`;
- suspension maps to `suspended` and emits an account-suspension invalidation;
- provider revocation is recorded against the provider-neutral external
  connection identified only by `(provider, provider_subject)`, maps to
  `provider_failed`, and emits a provider-revocation invalidation;
- entitlement revocation maps to `unauthorized_product` and remains a
  product-access decision;
- recovery is performed and verified by the external provider, then accepted
  by a lifecycle-authorized Zahir caller using an opaque recovery reference;
- Zahir unavailability maps to `zahir_unavailable` and does not itself emit a
  session invalidation, revoke, or extend a local session.

Products use those outcomes for sign-in, retry, recovery, or support actions.
Replaying a completed identity operation is explicit and does not create
another global account or local session. The deterministic mappings are in
`contracts/v1/authentication-lifecycle-fixtures.json`.

Lifecycle-authorized callers record provider revocation with
`POST /api/v1/accounts/{account}/identities/revocation` and accept externally
verified recovery with `DELETE` on the same path. Recovery requires
`accepted_recovery_reference`; Zahir stores only its hash in audit provenance.
Account resolution continues to return the original opaque account and adds
the frozen `account.authentication_outcome` plus a provider-neutral reason, so
a revoked identity cannot fall through to account creation.

## Laravel integration seam

Bind `AuthenticationConsumer` to a product-specific authenticated Zahir HTTP
client in a service provider. Routes/controllers own redirects and callback
transport. Middleware restores the local session and account projection.
Policies and gates load execution, repository, runner, Orb, and workspace
authorization separately after login.

Adoption checklist:

- [ ] Configure a Zahir issuer allowlist, product audience, service credential,
      exact callback/return allowlists, and session expiry policy.
- [ ] Bind `AuthenticationConsumer`, `AssertionDecoder`, `SigningKeyResolver`,
      and `SignatureVerifier`; keep implementation/provider SDK types private.
- [ ] Persist single-use state, nonce, PKCE material (when applicable), expiry,
      and consumed assertion IDs server-side.
- [ ] Add a unique local projection keyed by Zahir `account_id`; do not match or
      merge by email.
- [ ] Regenerate the local session after callback and invalidate it on logout,
      account suspension, entitlement revocation, or Zahir invalidation.
- [ ] Map every typed outcome to a deliberate UX and audit event.
- [ ] Keep onboarding in the product after authentication.
- [ ] Require independent execution, repository, runner/Orb, and workspace
      authorization before protected operations.
- [ ] Run the v1 fixture suite, including rotated and unknown key behavior.
- [ ] Keep live smoke tests and redacted deployment evidence outside the
      deterministic package suite.

## Ownership

External providers own credentials, MFA, recovery, provider sessions, and
identity verification. Zahir owns external identity mapping, opaque global
accounts, lifecycle, product registration and entitlements, assertion issuance,
key rotation, resolution, and audit provenance. Products own HTTP routes,
product-local users and sessions, onboarding, UI, preferences, and all domain
authorization and grants.
