# Pre-deployment security review

Review date: 2026-08-29. Scope: Zahir service, `sifrious/accounts-client`, and the Logres host integration through the commits listed in `release/components.json`.

## Threat review

| Area | Controls and evidence | Residual assessment |
|---|---|---|
| Callback attacks | Exact callback/logout allowlists, state, nonce, PKCE, short transaction expiry, issuer/audience/time/signature verification, generic host failure route | Live AuthKit configuration smoke test remains externally gated |
| Replay | Browser transaction is pulled before exchange; link and unlink replay are idempotent; tests cover consumed state and repeated operations | Low |
| Linking/collision | Fresh verified assertion, current opaque target assertion, database uniqueness, generic collision response, subject hashes in audit | Product service credentials are trusted to assert their own current session account; compromise requires immediate credential revocation |
| Confused deputy | Caller-specific credentials and audit, current-account equality check, lifecycle capability separate and default false | Administrative authority must be selected before enabling lifecycle capability |
| Service credentials | Hash-only storage, identifiable credentials, overlap rotation, expiry/revocation, caller disable, rate/body limits | Production secret injection and operator access are external gates |
| Enumeration | Opaque random account IDs, generic collision/unlink outcomes, no owning-account disclosure | Authorized callers can distinguish known account IDs on some administrative contracts; IDs have high entropy and callers are audited |
| Logging | Structured allowlisted fields, subject/email/token redaction tests, no exception messages or provider payloads | Log sink access/retention must be configured at deployment |
| Backup exposure | Backup prerequisite, checksum/retention record, isolated restore rehearsal, no backup in repository artifacts | Production encryption, key custody, and storage policy are infrastructure-owned |

## Findings

- **H-01 — JWKS rotation could fail until process restart. Resolved.** The driver now refreshes JWKS once after a cached-key verification failure and still fails closed. Deterministic rotation coverage proves old-key then new-key verification.
- **M-01 — Live WorkOS token/nonce and registered-URL behavior is unproven. Open external gate.** Required action: run the approved sandbox smoke test using exact deployment configuration before production.
- **M-02 — Production lifecycle administrator is not selected. Open external gate.** The capability remains false by default; do not enable it until an accountable owner and recovery procedure are approved.
- **M-03 — Production backup/log encryption and access policy is not configured. Open external gate.** Required action: infrastructure owner supplies evidence before deployment.
- **L-01 — A compromised product service can assert arbitrary opaque current-account IDs. Accepted by current service trust boundary.** Detection uses caller/credential/request audit; containment is credential or caller revocation. Revisit with account-scoped proof or workload identity when ZAHIR-019 triggers.

No high-severity finding remains unresolved. Repository-level remediation is complete. Accountable security-owner acceptance of M-01 through M-03 remains required before production configuration.
