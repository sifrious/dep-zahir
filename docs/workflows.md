# Zahir workflows

## Resolve a verified identity

1. A product adapter completes authorization code + PKCE and validates state, nonce, issuer, audience, signature, time claims, callback allowlist, and replay.
2. It converts the assertion into provider-neutral `VerifiedExternal` data.
3. The authenticated product calls Zahir's resolution contract.
4. Zahir looks up `(provider, provider_subject)`, creates an opaque account only when absent, refreshes safe claims, and records provenance.
5. The product projects the returned account ID into its own local user/session.

## Decide an entitlement

1. The authenticated product sends opaque account ID, product key, and entitlement name.
2. Zahir denies suspended accounts and inactive products.
3. Zahir evaluates non-revoked grants within their validity interval.
4. Zahir returns the provider-neutral decision; the product applies its own authorization and onboarding policy.
