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

Laravel session storage defaults to files because the external-only authentication schema does not own the scaffold's database session table.

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

The unused Laravel welcome specimen is removed so the public product cannot expose framework branding instead of the Accounts trust surface.

## Reusable Accounts client — 2026-08-27

SEAM: Places login integration and authenticated Accounts API calls in a shared application package.

PAYS WHEN: Logres and later applications integrate consistently without embedding provider-specific code.

CHARGES WHEN: Product behavior or provider objects leak into the shared package.

TRIGGER: Connected applications need one maintained integration path for Accounts.

## dep: stripe/stripe-php — 2026-08-27, ACC-014

SEAM: borrowed — serviced by Stripe and contributors; transitive: 1 package.

PAYS WHEN: Accounts must verify Stripe webhook signatures and create Stripe Checkout and Billing Portal sessions against Stripe's current API without maintaining payment-protocol code.

CHARGES WHEN: Stripe SDK major versions change their pinned API version; removal requires replacing calls confined to the Stripe adapter and webhook verifier.

TRIGGER: Stripe is selected for the Accounts launch and paid commercial state must produce product entitlements.

Signals: stable v21.3.0 resolved on 2026-08-27; active releases in July and August 2026; at least 100 contributors returned by GitHub; 12 open issues and 3 open pull requests observed; maintained by Stripe.

## dep: sifrious/official-burd-design — 2026-08-27, ACC-017

SEAM: borrowed — serviced in the local Official Burd Design repository; transitive: 0 new packages because its Illuminate requirements are already supplied by Laravel.

PAYS WHEN: Accounts pricing, billing, product, and policy pages need the same production Blade components and visual tokens as Logres without copying the design system.

CHARGES WHEN: A breaking component or stylesheet release requires updating the Accounts views; removal requires replacing the Burd component tags and stylesheet import across the public view family.

TRIGGER: Accounts now has multiple related public commerce surfaces and the standalone production design package is available.

Signals: release boundary commit `645da43` dated 2026-08-27; one current repository contributor; maintained alongside its consuming products; no external issue tracker yet.

The Vite entry compiles only the Burd-backed production stylesheet. The scaffold's Bunny font fetch and Tailwind transform are removed because Burd supplies self-hosted fonts and complete visual tokens.

## Public product behavior references — 2026-08-27

SEAM: Publishes each product's purpose, inputs, workflow, outputs, interactions, ownership boundaries, billing entitlement, availability, and current limits from the central product registry.

PAYS WHEN: Customers and integrators need one accurate reference for how a product behaves without reconstructing its contract from marketing copy or implementation repositories.

CHARGES WHEN: Detailed product documentation outgrows configuration-backed content or begins duplicating runtime state owned by the product.

TRIGGER: Logres has a planned end-to-end MVP workflow that must be visible without implying the execution service is already available.

The registry now publishes Accounts Client, Aleph, Bindle, Burdgeon, Funes, Kilgore, Logres, and Menard as in-development product families. Each family has a Free capability list and a future Paid capability list. Free plans bypass Stripe Price requirements; Paid plans remain unpurchasable and block the readiness check until their public prices and Stripe Price IDs are configured.
