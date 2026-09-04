# Authentication and account incident response

## Provider outage

Keep existing product sessions subject to their normal lifetime, but do not create new sessions from unverifiable callbacks. The callback fails closed with a generic error. Confirm provider status without weakening state, nonce, signature, issuer, audience, or time validation. Resume after a signed local fixture check and live smoke test pass.

## Zahir outage

Logres protected actions fail closed with HTTP 503; no local role or onboarding state may bypass the entitlement decision. Preserve request IDs and latency/dependency telemetry. Restore Zahir or execute the rehearsed database restore; do not introduce direct product database access or a local entitlement override.

## Credential or identity compromise

Revoke the affected service credential immediately; disable its caller if scope is uncertain. For provider-user compromise, rely on the provider's credential/session recovery, record the external connection as `provider_revoked`, and suspend the Zahir account through a lifecycle-authorized caller when necessary. Provider revocation invalidates matching product-local sessions and directs the user to provider recovery; it must not be classified as a retryable Zahir outage. Review resolution, lifecycle, entitlement, and service-request audits by bounded time range. Do not search logs using raw provider subjects or email.

## Suspension and recovery

Suspension denies entitlement even if a product session or local role remains. Reactivation requires lifecycle authority and a reason. Removing the last identity or accepting provider-verified recovery requires an accepted recovery reference and lifecycle authority. Preserve only the reference hash in lifecycle audit; never copy recovery secrets. A `recovered` result does not itself create a product session: require fresh authentication, and verify that retry resolves the same opaque account.

## Rollback

Use the exact release manifest and the backup/rollback runbook. Stop writes before schema rollback or database restore, verify authoritative row categories and contracts, then resume traffic. Record the incident timeline, affected commits/configuration, credential actions, and final verification evidence.
