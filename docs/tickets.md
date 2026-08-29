# Zahir delivery backlog

This is the dependency-ordered backlog from foundation to a production-proven first consumer. Routine implementation proceeds without repeated approval. Work pauses only at explicit external gates.

## Completed foundation

| ID | Outcome | Evidence |
|---|---|---|
| MME-1531 / ZAHIR-001 | Opaque accounts, unique identity mappings, explicit linking semantics, lifecycle, claims, and provenance | `b7f4d5d`; Zahir tests |
| MME-1532 / ZAHIR-002 | WorkOS decision, protocol contract, and provider-neutral adapter boundary | ADR-003; adapter tests |
| MME-1533 / ZAHIR-003 | Authenticated resolution and entitlement contracts | API tests and protected routes |
| ZAHIR-004 | Reusable client aligned with finalized contracts | `dep-accounts-client` `dd3d521`; PHPUnit and PHPStan |

## Wave 2 — deterministic integration

### ZAHIR-005 — Publish contract fixtures and compatibility tests

Repositories: `dep-zahir`, `dep-accounts-client`.

Acceptance criteria:

- Canonical success, denial, validation, authentication, not-found, and collision fixtures exist.
- Service and client test the same versioned fixtures.
- Breaking response changes fail CI.
- Fixtures contain no provider objects or secrets.

### ZAHIR-006 — Implement the WorkOS AuthKit login driver

Repository: `dep-accounts-client`; provider code remains behind `LoginDriver`.

Acceptance criteria:

- Authorization code + PKCE initiation is implemented.
- State, nonce, expiration, exact callback allowlisting, and replay protection fail closed.
- Issuer, audience/client ID, signature/JWKS, and time claims are verified.
- Callback output is provider-neutral `VerifiedExternal`.
- Logout and exact post-logout redirect allowlisting are implemented.
- Deterministic tests use local signed fixtures without WorkOS traffic.

External gate: live WorkOS credentials and registered URLs are needed only for smoke testing.

### ZAHIR-007 — Harden service authentication and rotation

Repository: `dep-zahir`.

Acceptance criteria:

- Caller credentials support identity, overlap rotation, revocation, and audit attribution.
- Tokens are never logged or persisted.
- Missing, malformed, revoked, and wrong-caller credentials fail closed.
- Rate and request-size limits protect both endpoints.
- Rotation and emergency-revocation runbooks exist.

External gate: deployment secret injection for production verification.

### ZAHIR-008 — Complete linking and lifecycle contracts

Repositories: `dep-zahir`, `dep-accounts-client`.

Acceptance criteria:

- Linking requires a current target account and newly verified assertion.
- Cross-account collisions expose no owning-account information.
- Replay is idempotent; unlink cannot remove the last usable identity without an accepted recovery path.
- Suspension/reactivation are authenticated, audited, and separate from product authorization.
- Products receive opaque outcomes and never mutate Zahir storage.

External gate: administrative authority selection before production lifecycle mutation.

## Wave 3 — first consumer

### ZAHIR-009 — Scaffold the Logres host boundary

Repository: `logres-site`; never framework-neutral `dep-logres`.

Acceptance criteria:

- Laravel host has configuration, health checks, test database, and dependency wiring.
- It consumes `dep-logres` and `dep-accounts-client` without copying either domain.
- Secrets are configuration-only and absent from source control.

### ZAHIR-010 — Integrate WorkOS login into Logres

Repository: `logres-site`, using ZAHIR-006.

Acceptance criteria:

- Login, callback, logout, and failure routes are host-owned.
- Verified identity resolves through Zahir into a local user projection keyed by opaque account ID.
- Sessions contain no provider credentials; Logres never becomes global identity authority.
- Suspended accounts cannot establish authorized sessions.
- Replay, state/nonce failure, issuer/audience mismatch, and Zahir outage are tested.

External gate: WorkOS sandbox credentials and registered URLs for live verification.

### ZAHIR-011 — Bootstrap Logres product and entitlement policy

Repository: `dep-zahir` plus deployment seed/configuration.

Acceptance criteria:

- `logres` product is stable and idempotently provisioned.
- Initial entitlement name is consistent across service, client, and host.
- Deterministic development/test grants exist.
- Production grant ownership and revocation source are explicit; no payment data enters Zahir.

External gate: approve launch access as manual/invitation, free-by-default, or externally provisioned. Until then production denies by default.

### ZAHIR-012 — Enforce entitlements in Logres

Repository: `logres-site`; `dep-logres` remains authentication-framework neutral.

Acceptance criteria:

- Protected actions request the canonical Zahir entitlement.
- Allow, deny, suspended, unknown account, timeout, and stale decision behavior are tested.
- Default fails closed unless a separately accepted outage-grace policy applies.
- Local roles/onboarding cannot elevate the Zahir entitlement.

External gate: outage-grace duration only if strict fail-closed is unacceptable.

## Wave 4 — operations and launch

### ZAHIR-013 — Add observability and safe audit operations

- Structured logs/metrics cover outcomes, collisions, auth failures, denials, latency, and dependency errors.
- Provider subjects, emails, tokens, assertions, and secrets never enter logs.
- Audit retention/access/deletion procedures are documented.
- Readiness distinguishes process and database health.

### ZAHIR-014 — Add CI, migration, backup, and rollback gates

- All repositories run formatting, tests, static analysis, contract compatibility, and migration checks.
- Production migrations have backup prerequisites and rollback rehearsal.
- Restore verification preserves accounts, mappings, entitlements, and provenance.
- Dependency and secret scanning are enabled.

### ZAHIR-015 — Deploy Zahir infrastructure

- Application, database, TLS/domain, secrets, logs, backups, and alerts are configured.
- Migrations are observable and rollback/credential rotation are exercised.
- No product receives database credentials.

External gate: hosting/domain authority and production secret access.

### ZAHIR-016 — Configure WorkOS production application

- Exact callback/logout URLs are registered.
- Issuer and audience match deployment configuration.
- Development/unused redirects are absent.
- Key rotation and emergency revocation are documented.

External gate: WorkOS administrative access.

### ZAHIR-017 — Prove the end-to-end launch path

- New identity resolves once; reauthentication resolves the same account.
- Email change preserves identity; equal emails do not merge.
- Entitled access succeeds; non-entitled/suspended access fails.
- Logout clears the product session with allowlisted redirects.
- Audit proves provenance without leaking assertions.
- No product has Zahir database access.

External gate: authorized production test identity and smoke-test approval.

### ZAHIR-018 — Security review and launch sign-off

- Review covers callback attacks, replay, linking, confused deputy, service credentials, enumeration, logging, and backup exposure.
- High-severity findings are resolved or explicitly accepted.
- Runbook covers provider/Zahir outage, compromise, suspension, and rollback.
- Launch evidence references exact deployed commits/configuration.

External gate: accountable security/launch owner sign-off.

## Post-launch triggers

- **ZAHIR-019 — Replace static caller tokens with workload identity:** when infrastructure supports it or rotation becomes burdensome.
- **ZAHIR-020 — Add a second product consumer:** after a real product is selected, to prove neutrality.
- **ZAHIR-021 — Add a second identity provider:** only for a real provider requirement.
- **ZAHIR-022 — Add organizations/memberships:** only when multiple people must share one product account.

## Execution policy

Proceed in dependency order without repeated confirmation for repository-local implementation, tests, documentation, commits, and pushes to the named repositories. Pause only at external gates involving credentials, production mutation, hosting/domain authority, business access policy, outage policy, or accountable sign-off.
