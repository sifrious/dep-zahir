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

## Service authentication

The first internal contract uses caller-specific bearer credentials. Credentials are deployment secrets and are compared in constant time. The authenticated caller name is attached to audit events. A future signed-service-token decision may replace this without changing domain contracts.
