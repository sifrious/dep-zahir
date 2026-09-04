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

## Authentication consumer contract — 2026-09-04, MME-1823

TRIGGER: Burdgeon and later products need one versioned Zahir login seam without
provider SDK or authorization coupling.

SEAM: `Sifrious\Zahir\Authentication\V1`, deterministic v1 fixtures, and the
Laravel adoption contract.

BOUNDARY: authenticated login includes global account and product entitlement
only. Runner enrollment, execution permission, and repository/workspace grants
remain separate product-owned decisions.

REVISIT: add deployment-configured issuer/key URLs and redacted live evidence
without changing the v1 public types.
