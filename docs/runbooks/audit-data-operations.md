# Audit data operations

Zahir emits structured events for caller authentication, account resolution/collisions, entitlement outcomes, dependency status, and request latency. Metrics backends may aggregate `metric_count` and `latency_ms`. Logs must never include bearer tokens, raw assertions, provider subjects, email addresses, provider payloads, or secrets.

Database audit records retain opaque account IDs, caller/credential attribution, outcomes, timestamps, safe provenance, and SHA-256 provider-subject hashes. Access is restricted to the incident-response and account-operations roles through read-only production access. Every export must have a case reference and bounded time range.

Default retention is 400 days unless legal/privacy owners approve another period. Deletion runs in bounded batches by `occurred_at`, records counts and cutoff, and never cascades into accounts, identity mappings, or entitlements. Account-erasure requests must follow the separately approved identity/account lifecycle process; operators must not hand-edit audit tables.

Quarterly verification samples logs and audit exports for prohibited values, checks retention deletion, and confirms `/up` process liveness remains distinct from database/configuration readiness checks.
