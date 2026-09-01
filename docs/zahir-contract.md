# Zahir account and entitlement contract

## Domain

An account ID is generated internally as `acc_` plus a random ULID. It is opaque and never derived from email, provider, or provider subject. Account status is `active` or `suspended`.

An external identity is uniquely keyed by `(provider, provider_subject)`. Replaying that key resolves the same account and refreshes only allowlisted claims and provenance. Equal emails never merge identities. A second verified identity joins an account only through the explicit linking operation. A key already owned by another account fails closed.

Safe claims are limited to `email`, `email_verified`, and `name`. They are mutable metadata, not identity. Provenance records issuer, audience, assertion time, and optional assertion ID. Audit events hash the provider subject rather than duplicating it.

## Provider-neutral resolution input

```json
{
  "external": {
    "provider": "workos",
    "provider_subject": "user_...",
    "claims": {"email": "person@example.com", "email_verified": true, "name": "Person"},
    "provenance": {
      "issuer": "https://api.workos.com/",
      "audience": "client_...",
      "asserted_at": "2026-08-29T12:00:00Z",
      "assertion_id": "optional-jti"
    },
    "authenticated_at": "2026-08-29T12:00:00Z"
  }
}
```

The caller asserts that the identity passed the protocol checks in ADR-003. Zahir does not accept raw codes, tokens, provider sessions, or WorkOS objects.

Response: `{"account":{"id":"acc_...","status":"active","created":true}}`.

## Entitlement contract

Request: `{"account_id":"acc_...","product":"logres","entitlement":"access"}`.

The response contains `allowed`, `account_id`, `account_status`, `product`, `entitlement`, `evaluated_at`, and nullable `grant_id`. Suspended accounts, inactive products, absent grants, future grants, expired grants, and revoked grants deny access.

## Identity linking and unlinking

`POST /api/v1/accounts/{account}/identities/link` accepts the same provider-neutral `external` assertion as resolution. The authenticated product must also send its current opaque session account in `X-Zahir-Current-Account`, exactly matching the target. Assertions must be newly authenticated within the configured short window. Replay on the same account is idempotent; an identity owned elsewhere returns only `Identity linking failed` and never reveals its owner.

`DELETE /api/v1/accounts/{account}/identities` accepts `provider` and `provider_subject`. Repeating an unlink returns the opaque `unchanged` outcome. Removing the last usable identity fails unless a lifecycle-authorized caller supplies an accepted recovery reference; that refusal carries the stable `reason` code `recovery_required`, which is the only way an unlink fails. Naming it tells a product nothing it does not already know about its own account, and without it the caller cannot tell "offer recovery" from "something broke".

## Account lifecycle

Lifecycle-authorized callers may suspend with `POST /api/v1/accounts/{account}/suspension` and reactivate with `DELETE` on the same path. Both require a reason and create a distinct lifecycle audit event. Ordinary product callers receive HTTP 403. No caller receives storage handles or mutates Zahir tables directly.

Repeating a lifecycle request is safe: the status settles once, while every attempt is still audited so each remains attributable. Suspension is an access decision and never a deletion — the account, its identity mappings, and its grants all survive, so reactivation restores exactly what was there and a product's own data is never orphaned. Revoking a grant likewise closes one product without touching the account or any other product's access.

## Product session invalidation

Zahir holds no session state and cannot end a product session directly. Products re-ask the entitlement contract on protected requests and refuse any decision older than their configured freshness window, so suspension and revocation take effect within one decision window rather than one session lifetime. This is the whole invalidation contract; there is no callback, outbox, or push.

## Service authentication

The internal contract uses caller-specific, hash-only bearer credentials. The authenticated caller and credential are attached to audit events. Account-lifecycle authority is a separate caller capability and defaults off. A future workload-identity decision may replace this without changing domain contracts.
