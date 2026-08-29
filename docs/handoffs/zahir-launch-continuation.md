# Agent Handoff: Zahir launch continuation

Status: READY

## Identity

- Tickets: ZAHIR-005 through ZAHIR-018B, ordered in `docs/tickets.md`
- Workspace: MISSING; non-blocking because all repository checkouts and remotes are explicit
- Primary repository: `sifrious/dep-zahir` — `git@github.com-sifrious:sifrious/dep-zahir.git`
- Primary checkout: `/Users/mme/gits/sifrious/dep-zahir`, branch `main`, baseline `34158c6`
- Client repository: `sifrious/dep-accounts-client` — `git@github.com-sifrious:sifrious/dep-accounts-client.git`
- Client checkout: `/Users/mme/gits/sifrious/dep-accounts-client`, branch `main`, baseline `dd3d521`
- Logres domain repository: `sifrious/dep-logres` — `git@github.com-sifrious:sifrious/dep-logres.git`
- Logres domain checkout: `/Users/mme/gits/sifrious/dep-logres`, branch `main`, baseline `1435233`
- Logres host repository: `sifrious/logres-site` — `git@github.com-sifrious:sifrious/logres-site.git`
- Logres host checkout: `/Users/mme/gits/sifrious/logres-site`, unborn `main` with no commits
- Execution surface: Codex implementation agent

All listed working trees were clean when this handoff was prepared.

## Objective

Take Zahir from its completed provider-neutral account foundation to a production-proven WorkOS-to-Zahir-to-Logres authentication and entitlement path, executing repository-local tickets continuously in prerequisite order and stopping only at explicit external gates.

## Scope and boundary

- In scope: ZAHIR-005, 014A, 006, 007, 009, 011, 008, 010, 012, 013, 014B, 018A, 015, 016, 017, and 018B.
- In scope: contract fixtures, CI, WorkOS adapter, service authentication, identity linking/lifecycle, Logres host integration, entitlement bootstrap/enforcement, observability, release safety, security review evidence, deployment preparation, and launch verification.
- Out of scope: passwords, MFA secrets, recovery credentials, provider sessions in Zahir, payment methods, product profiles/preferences in Zahir, automatic email linking, shared databases, speculative organizations, and provider objects in public contracts.
- Package-vs-app boundary: `dep-zahir` owns global accounts, external mappings, lifecycle, products, entitlements, resolution, and audit. `dep-accounts-client` owns reusable provider-neutral client contracts and hides provider adapters behind `LoginDriver`. `logres-site` owns Laravel routes, callbacks, sessions, local user projection, onboarding, and product authorization. `dep-logres` stays framework-neutral and must not acquire Laravel, OAuth/OIDC, Eloquent, session, or WorkOS dependencies.

## Required execution order

1. ZAHIR-005 — shared contract fixtures.
2. ZAHIR-014A — baseline CI gates.
3. Execute ZAHIR-006, ZAHIR-007, ZAHIR-009, and ZAHIR-011 after 005/014A; these may run in parallel.
4. ZAHIR-008 — linking and lifecycle contracts.
5. ZAHIR-010 — Logres WorkOS login.
6. ZAHIR-012 — Logres entitlement enforcement.
7. ZAHIR-013 — observability and safe audit operations.
8. ZAHIR-014B — release, migration, backup, and rollback gates.
9. ZAHIR-018A — pre-deployment security review.
10. ZAHIR-015 and ZAHIR-016 — deployment and WorkOS production configuration; may proceed in parallel only after 018A.
11. ZAHIR-017 — end-to-end production proof.
12. ZAHIR-018B — final launch sign-off.

Do not skip a prerequisite. Read each ticket's acceptance criteria and external gate in `docs/tickets.md` before starting it.

## Relevant architecture decisions

- Global account IDs are internally generated opaque `acc_...` identifiers — source: `docs/zahir-contract.md`.
- External identity key is exactly `(provider, provider_subject)`; email is mutable metadata and never links accounts — source: `AGENTS.md` and `docs/zahir-contract.md`.
- WorkOS AuthKit is provider #1 using authorization code + PKCE; Zahir remains provider-neutral — source: `docs/decisions/ADR-003-workos-authkit.md`.
- Products never query Zahir storage and never become global identity authority — source: `AGENTS.md`.
- Products own OAuth/OIDC transport, callback, local session, local user projection, authorization, onboarding, and UI — source: `AGENTS.md`.
- Zahir's first service-auth contract uses caller-specific bearer credentials; hardening and rotation are ZAHIR-007 — source: `docs/zahir-contract.md`.
- Canonical prerequisite graph and ticket acceptance criteria — source: `docs/tickets.md` at baseline `34158c6`.

## Acceptance criteria

- [ ] Every ticket is executed only after its declared prerequisites.
- [ ] Each ticket satisfies every criterion in `docs/tickets.md` with observable test, static-analysis, fixture, migration, or operational evidence.
- [ ] Public Zahir and client contracts contain no WorkOS SDK objects.
- [ ] Email change preserves account identity and equal emails never merge accounts.
- [ ] Logres integrates in `logres-site`; `dep-logres` remains framework-neutral.
- [ ] Product applications have no Zahir database credentials or storage coupling.
- [ ] Invalid state/nonce, callback replay, issuer/audience/signature failure, suspended accounts, absent entitlements, and dependency outages fail closed according to recorded policy.
- [ ] Sensitive provider assertions, subjects, emails, credentials, and tokens do not enter logs.
- [ ] CI and release gates identify exact tested commits and prevent contract drift.
- [ ] Production deployment and live smoke testing occur only after their external gates are satisfied.
- [ ] Final launch sign-off references exact deployed commits and configuration versions.

## Verification

- In `/Users/mme/gits/sifrious/dep-zahir`: `vendor/bin/pint --test && php artisan test --compact` — formatting and Zahir unit/feature contracts.
- In `/Users/mme/gits/sifrious/dep-zahir`: `php artisan migrate:fresh --force && php artisan migrate:fresh --force` — replay-safe clean migration construction.
- In `/Users/mme/gits/sifrious/dep-zahir`: `php artisan route:list --path=api` — versioned routes and service middleware.
- In `/Users/mme/gits/sifrious/dep-accounts-client`: `composer check` — client PHPUnit and PHPStan.
- In `/Users/mme/gits/sifrious/dep-logres`: `composer check` — confirms framework-neutral Logres remains healthy if touched.
- In `/Users/mme/gits/sifrious/logres-site`: verification command is MISSING until ZAHIR-009 creates the host scaffold; non-blocking for earlier tickets. ZAHIR-009 must establish and document its exact full-suite command before completion.
- In every changed repository: `git diff --check` and repository-specific CI configuration validation.
- For ZAHIR-005 onward: run the shared contract-fixture compatibility check introduced by ZAHIR-005.
- Live WorkOS and production smoke tests: NOT RUN until the corresponding external gate is satisfied.

## Constraints

- Read every repository's `AGENTS.md` before editing it. Re-read if it changes.
- Preserve unrelated user changes; verify working-tree status before each ticket.
- Use deterministic local WorkOS fixtures; do not require network access in normal tests.
- Never commit credentials, raw tokens, provider sessions, production assertions, or copied secrets.
- Do not weaken collision, authentication, claim allowlist, or entitlement failure behavior to make tests pass.
- Do not expose lifecycle mutation before administrative authority is selected.
- Production entitlement defaults to deny until launch access policy is approved.
- Strict fail-closed is the default during Zahir outage unless an outage-grace policy is explicitly accepted.
- Repository-local implementation, tests, documentation, commits, and pushes to the named repositories are authorized to proceed continuously.
- Stop at an external gate; do not fabricate credentials, production authority, domain ownership, business access policy, outage policy, security acceptance, or launch sign-off.

## External stop gates

- ZAHIR-006/010 live verification: WorkOS sandbox credentials and registered callback/logout URLs.
- ZAHIR-007 production verification: deployment secret-injection access.
- ZAHIR-008 production lifecycle mutation: administrative authority selection.
- ZAHIR-011 production grants: approved manual/invitation, free-default, or external provisioning policy.
- ZAHIR-012 outage behavior: an explicit grace duration only if strict fail-closed is rejected.
- ZAHIR-015: hosting project, domain authority, database, and production secret access.
- ZAHIR-016: WorkOS administrative access.
- ZAHIR-017: authorized production test identity and smoke-test approval.
- ZAHIR-018A/018B: accountable security and launch-owner decisions.

When a gate is reached, finish all other unblocked work first. Report the exact missing authority or value and the smallest next action required from its owner.

## Required report-back

- Outcome and COMPLETE/PARTIAL/BLOCKED status for each ticket attempted.
- Acceptance-criterion results with direct evidence.
- Exact tests/checks run with pass/fail/not-run results.
- Commits created and pushed per repository.
- Files changed and their purpose.
- Decisions made, where recorded canonically, and why they did not exceed existing authority.
- Blockers, unresolved questions, external gates, and deviations.
- Follow-up tickets proposed or created; do not create external tracker records without separate authority.
- Provenance: ticket IDs, repository/checkouts, branches, baseline and resulting commits, execution agent/run, and this handoff.

## Missing or conflicting context

- Linear ticket URLs/status: MISSING; non-blocking for repository execution. Linear is the canonical owner if these IDs are later created there.
- Stacks workspace identity: MISSING; non-blocking because exact checkouts/remotes are supplied.
- Logres production access policy: MISSING; blocking only at ZAHIR-011 production provisioning. Owner: business/product owner.
- Outage-grace policy: MISSING; non-blocking because strict fail-closed is the documented default. Owner: product/operations owner if a grace period is desired.
- Hosting/domain and WorkOS credentials: MISSING; blocking only at ZAHIR-015 through 017. Owners: infrastructure and WorkOS administrators.
- `logres-site` scaffold and verification command: MISSING; non-blocking before ZAHIR-009 and created by that ticket.

## Source map

- Ticket order, prerequisites, gates, and acceptance criteria — `/Users/mme/gits/sifrious/dep-zahir/docs/tickets.md`, baseline `34158c6`.
- Zahir ownership and identity invariants — `/Users/mme/gits/sifrious/dep-zahir/AGENTS.md` and `docs/zahir-contract.md`.
- WorkOS protocol decision — `/Users/mme/gits/sifrious/dep-zahir/docs/decisions/ADR-003-workos-authkit.md`.
- Zahir non-goals — `/Users/mme/gits/sifrious/dep-zahir/DO-NOT-BUILD.md`.
- Client boundary and verification — `/Users/mme/gits/sifrious/dep-accounts-client/AGENTS.md` and `README.md`.
- Framework-neutral Logres boundary — `/Users/mme/gits/sifrious/dep-logres/README.md`.
- Prior execution evidence — Zahir commits `b7f4d5d`, `102d507`, `34158c6`; client commit `dd3d521`.
