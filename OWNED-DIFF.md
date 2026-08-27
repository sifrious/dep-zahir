# Owned diff

The baseline is the unmodified Laravel scaffold. Entries are added before a deviation is merged.

## Central account boundary — 2026-08-27

SEAM: Adds an account-and-entitlement service between product applications and external identity and payment providers.

PAYS WHEN: A second product needs the same account, identity mapping, or entitlement without sharing application databases.

CHARGES WHEN: The service duplicates capabilities already supplied portably by the selected identity platform or becomes an availability bottleneck for every product.

TRIGGER: Logres is becoming a hosted product and future products require one login and account identity.

## Provider identity mapping — 2026-08-27

SEAM: Maps an external issuer and subject to an internal stable account ID.

PAYS WHEN: Authentication providers change or multiple login methods belong to one account.

CHARGES WHEN: Provider-specific claims leak beyond the adapter and become application schema.

TRIGGER: Authentication is explicitly external while product identity must remain provider-independent.

## Product entitlements — 2026-08-27

SEAM: Separates the permission to use a product capability from payment-provider subscription objects.

PAYS WHEN: Logres must authorize access independently of checkout, refunds, trials, or provider-specific plan structures.

CHARGES WHEN: Entitlements become a speculative policy engine instead of named product capabilities.

TRIGGER: Logres requires one centrally managed `logres.access` decision before launch.

## External-only authentication schema — 2026-08-27

SEAM: Removes the scaffold's local password, password-reset, remember-token, and session-owned user schema in favor of provider-scoped external identity links.

PAYS WHEN: Authentication credentials, recovery, multifactor authentication, and sessions remain outside Accounts while internal account identity survives provider changes.

CHARGES WHEN: A product assumes the external identity record is itself an authenticated application user or provider claims leak into authorization rules.

TRIGGER: External login is a declared product constraint and local credentials would create a second authentication authority.

## ULID public identities — 2026-08-27

SEAM: Uses Laravel's built-in ULID support for account, identity, product, and grant identifiers.

PAYS WHEN: Identifiers cross application and provider boundaries without exposing database sequence or requiring coordination.

CHARGES WHEN: A consumer treats sortable identity as business chronology instead of using explicit timestamps.

TRIGGER: Logres must persist stable Account references without sharing the Accounts database.

## Public trust center — 2026-08-27

SEAM: Publishes versioned legal and compliance documents from Accounts at stable public URLs.

PAYS WHEN: Every product can link to one approved source instead of copying policy text.

CHARGES WHEN: Sensitive operational evidence is confused with public compliance information or the service begins drafting legal conclusions.

TRIGGER: Accounts is designated as the public home for legal and compliance information.

## Reusable Accounts client — 2026-08-27

SEAM: Places login integration and authenticated Accounts API calls in a shared application package.

PAYS WHEN: Logres and later applications integrate consistently without embedding provider-specific code.

CHARGES WHEN: Product behavior or provider objects leak into the shared package.

TRIGGER: Connected applications need one maintained integration path for Accounts.
