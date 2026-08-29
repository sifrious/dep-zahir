# Zahir delivery backlog

This is the dependency-ordered backlog from foundation to a production-proven first consumer. Routine implementation proceeds without repeated approval. Work pauses only at explicit external gates.

## Canonical prerequisite order

This is the execution order. A ticket may start only after every item in `Requires` is complete. Tickets shown at the same stage may run in parallel.

| Stage | Ticket | Requires | Blocks |
|---:|---|---|---|
| 0 | ZAHIR-001 — Account and identity domain | None | 003, 008, 011 |
| 0 | ZAHIR-002 — WorkOS decision and neutral adapter boundary | None | 006 |
| 0 | ZAHIR-003 — Resolution and entitlement contracts | 001 | 004, 007, 008, 011 |
| 0 | ZAHIR-004 — Reusable client alignment | 003 | 005, 006, 008, 009 |
| 1 | ZAHIR-005 — Shared contract fixtures | 003, 004 | 014A |
| 2 | ZAHIR-014A — Baseline CI gates | 005 | 006, 007, 008, 009, 010, 011, 012, 013 |
| 3A | ZAHIR-006 — WorkOS login driver | 002, 004, 005, 014A | 010, 016 |
| 3A | ZAHIR-007 — Service authentication hardening | 003, 005, 014A | 008, 010, 012, 013 |
| 3A | ZAHIR-009 — Logres host scaffold | 004, 005, 014A | 010, 012 |
| 3A | ZAHIR-011 — Logres product/entitlement bootstrap | 001, 003, 005, 014A | 012 |
| 4 | ZAHIR-008 — Linking and lifecycle contracts | 005, 007 | 013, 018A |
| 5 | ZAHIR-010 — Logres WorkOS login | 006, 007, 009 | 012, 016, 018A |
| 6 | ZAHIR-012 — Logres entitlement enforcement | 007, 009, 010, 011 | 013, 018A |
| 7 | ZAHIR-013 — Observability and audit operations | 007, 008, 012 | 014B, 018A |
| 8 | ZAHIR-014B — Release, migration, backup, and rollback gates | 006, 007, 008, 009, 010, 011, 012, 013, 014A | 018A, 015 |
| 9 | ZAHIR-018A — Pre-deployment security review | 008, 010, 012, 013, 014B | 015, 016 |
| 10A | ZAHIR-015 — Deploy Zahir infrastructure | 014B, 018A | 017 |
| 10A | ZAHIR-016 — Configure WorkOS production application | 006, 010, 018A | 017 |
| 11 | ZAHIR-017 — End-to-end launch proof | 012, 013, 015, 016 | 018B |
| 12 | ZAHIR-018B — Final launch sign-off | 017 | Launch |

The `3A` tickets are independent after baseline CI and may run in parallel. ZAHIR-015 and ZAHIR-016 may also run in parallel after the pre-deployment security review. Post-launch tickets 019–022 are trigger-based and are not prerequisites for launch.

## Completed foundation

| ID | Outcome | Evidence |
|---|---|---|
| MME-1531 / ZAHIR-001 | Opaque accounts, unique identity mappings, explicit linking semantics, lifecycle, claims, and provenance | `b7f4d5d`; Zahir tests |
| MME-1532 / ZAHIR-002 | WorkOS decision, protocol contract, and provider-neutral adapter boundary | ADR-003; adapter tests |
| MME-1533 / ZAHIR-003 | Authenticated resolution and entitlement contracts | API tests and protected routes |
| ZAHIR-004 | Reusable client aligned with finalized contracts | `dep-accounts-client` `dd3d521`; PHPUnit and PHPStan |

## Execution status

| Ticket | Status | Evidence |
|---|---|---|
| ZAHIR-005 | Complete | Canonical v1 fixtures; Zahir 17 tests/52 assertions; client `bf6bfc2`, 3 tests/15 assertions and PHPStan |
| ZAHIR-014A | Complete | Zahir CI `3581b97` and client CI `22594d4`; pushed GitHub runs passed tests, analysis, migrations, audits, contract digests, and Gitleaks |
| ZAHIR-006 | Complete | `dep-accounts-client` `a849ab9`; 8 tests/122 assertions, PHPStan max, and contract checksum pass |
| ZAHIR-007 | Complete | `dfb3c42`; 20 tests/66 assertions, Pint, dependency audit, and fresh migration replay pass |
| ZAHIR-009 | Complete | `logres-site` `0184855`; 7 tests/19 assertions, Pint, audits, migrations, and production asset build pass |
| ZAHIR-011 | Complete | Zahir `b4003e6`, client `725dbf6`, host `8376fea`; all suites and deterministic seed replay pass |
| ZAHIR-008 | Complete | Zahir `6599693`, client `f56fd73`; 26 tests/103 assertions and 10 tests/128 assertions, analysis, audits, and migration replay pass |
| ZAHIR-010 | Complete | Client `53ed57e`, host `5213c80`; 12 tests/43 assertions, migrations/rollback, Pint, and audits pass; live smoke remains externally gated |
| ZAHIR-012 | Complete | `logres-site` `0765357`; 18 tests/53 assertions, Pint, and audit pass across allow/deny/suspend/unknown/timeout/stale/mismatch cases |
| ZAHIR-013 | Complete | Zahir `76833e2`, host `ae4d57f`; structured redaction tests pass with 26 tests/104 assertions and 18 tests/54 assertions |
| ZAHIR-014B | Complete | `352a994`; local rehearsal and GitHub CI run `33236125152` passed backup/restore counts, rollback/forward migration, manifest artifact, tests, scans, and audits |
| ZAHIR-018A | External gate | Review and incident runbook complete; JWKS rotation finding resolved in client `4400419`; no unresolved high findings; security-owner acceptance of M-01 through M-03 remains required |
| ZAHIR-015 | Not started | Requires ZAHIR-018A security-owner acceptance and deployment authority/infrastructure |
| ZAHIR-016 | Not started | Requires ZAHIR-018A security-owner acceptance and WorkOS production administration |
| ZAHIR-017 | Not started | Requires deployed Zahir, configured WorkOS application, and authorized production test identity |
| ZAHIR-018B | Not started | Requires ZAHIR-017 evidence, operational contacts, and accountable launch-owner decision |

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

### ZAHIR-014A — Add baseline CI gates

- Zahir and client pull requests run formatting, tests, static analysis, contract compatibility, and migration checks.
- Logres host CI is installed when ZAHIR-009 creates the application.
- Dependency and secret scanning are enabled before integration code grows.

### ZAHIR-014B — Add release, migration, backup, and rollback gates

- Production migrations have backup prerequisites and rollback rehearsal.
- Restore verification preserves accounts, mappings, entitlements, and provenance.
- Release artifacts identify exact service, client, and host commits.

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

### ZAHIR-018A — Pre-deployment security review

- Review covers callback attacks, replay, linking, confused deputy, service credentials, enumeration, logging, and backup exposure.
- High-severity findings are resolved or explicitly accepted.
- Runbook covers provider/Zahir outage, compromise, suspension, and rollback.
- Approved findings and required remediations are recorded before production configuration.

External gate: accountable security owner acceptance of any unresolved finding.

### ZAHIR-018B — Final launch sign-off

- ZAHIR-017 evidence references exact deployed commits and configuration versions.
- Every launch prerequisite is complete or has explicit accountable-owner acceptance.
- Operational ownership and incident contacts are recorded.
- The accountable launch owner records the go/no-go decision.

External gate: accountable launch owner sign-off.

## Post-launch triggers

- **ZAHIR-019 — Replace static caller tokens with workload identity:** when infrastructure supports it or rotation becomes burdensome.
- **ZAHIR-020 — Add a second product consumer:** after a real product is selected, to prove neutrality.
- **ZAHIR-021 — Add a second identity provider:** only for a real provider requirement.
- **ZAHIR-022 — Add organizations/memberships:** only when multiple people must share one product account.

## Execution policy

Proceed in dependency order without repeated confirmation for repository-local implementation, tests, documentation, commits, and pushes to the named repositories. Pause only at external gates involving credentials, production mutation, hosting/domain authority, business access policy, outage policy, or accountable sign-off.
