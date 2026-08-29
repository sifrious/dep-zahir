# Zahir owned differences

## Provider-neutral identity domain — 2026-08-29, ZAHIR-001

TRIGGER: portfolio products require one opaque account and explicit external identity mappings.

SEAM: `VerifiedExternal`, `AccountResolver`, account lifecycle, and persistence.

BOUNDARY: no credentials, sessions, payment data, product profiles, or implicit email linking.

## WorkOS adapter boundary — 2026-08-29, ZAHIR-002

TRIGGER: WorkOS AuthKit was approved as provider #1.

SEAM: `App\Identity\WorkOs` maps already verified claims to provider-neutral input.

BOUNDARY: WorkOS SDK objects and raw protocol artifacts do not cross into domain or public contracts.

## Authenticated service API — 2026-08-29, ZAHIR-003

TRIGGER: products must resolve accounts and query entitlements without storage coupling.

SEAM: versioned JSON endpoints protected by caller-specific bearer credentials.

REVISIT: replace static service credentials with signed workload identity when deployment infrastructure supplies it.
