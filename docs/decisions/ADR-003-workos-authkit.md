# ADR-003: WorkOS AuthKit as identity provider #1

Status: accepted, 2026-08-29

## Decision

Use WorkOS AuthKit as the first external identity provider. Zahir's provider identifier is `workos`; the canonical provider subject is the verified OIDC `sub` claim. This is an integration decision, not a domain dependency.

## Protocol contract

- Use OAuth 2.0/OIDC authorization-code flow with PKCE.
- The product adapter initiates authorization, generates and stores single-use state, PKCE verifier, and nonce, and owns callback and product session.
- Callbacks use exact allowlists, expire promptly, validate state and nonce, exchange the code once, and reject replay.
- Verify signatures using issuer discovery/JWKS. Cache within HTTP rules, refresh on unknown key ID, and fail closed on discovery or verification failure.
- Validate issuer, audience/client ID, expiry, not-before, and issued-at with a small documented clock tolerance.
- Issuer and client ID are deployment configuration, not domain constants.
- Logout ends the product-local session and uses provider logout when configured. Post-logout redirects use exact allowlists.
- Only a fully verified assertion becomes `VerifiedExternal`. Raw codes, JWTs, sessions, SDK users, and AuthKit objects never enter Zahir's public API.
- Allowlisted provider-independent claims are email, email-verification status, and name. Email never links accounts.

## Responsibilities

WorkOS owns credentials, passwordless/passkey/MFA behavior, recovery, social-login verification, and provider sessions. Product adapters own browser transport, callback state, nonce, PKCE, allowlists, local sessions, and authorization. Zahir owns account resolution, explicit identity linking, lifecycle, entitlements, and provenance.

## Deterministic tests

Unit tests use fixed verified claim maps and times with no WorkOS traffic. Integration tests use locally generated signed OIDC fixtures with fixed issuer, audience, nonce, and JWKS. Live WorkOS smoke tests are separate deployment checks.
