# Accounts

Accounts is the central account and product-entitlement service for Logres and future products.

External identity providers own credentials, sessions, multifactor authentication, and recovery. External payment providers own payment instruments, checkout, charges, subscriptions, invoices, and refunds. Accounts owns stable internal account identities, provider identity links, products, and named entitlement grants.

Products consume Accounts through authenticated service contracts. They do not connect directly to the Accounts database.

## Current capability

- Stable ULID account identities.
- Idempotent external issuer and subject resolution.
- Product registration.
- Time-bounded and revocable entitlement grants.
- Deterministic entitlement decisions.
- Suspended-account and inactive-product denial.
- A public trust center with stable legal and compliance document routes.
- A separately versioned Accounts client package for connected applications.
- Signed, idempotent Stripe subscription webhooks mapped to product entitlements.
- Stripe Checkout and Billing Portal session services behind the pending authenticated application boundary.
- Central product configuration feeding product, pricing, billing, Stripe-readiness, and policy surfaces.
- Official Burd Design Blade components and self-hosted visual assets.

The external identity provider and service-authentication protocol remain manual launch decisions. No public account-resolution or entitlement endpoint is exposed until those decisions are accepted.

Stripe configuration is documented in [docs/stripe.md](docs/stripe.md). The only public Stripe route is the signature-verified webhook at `/api/stripe/webhooks`.

The root URL is the public product and business site used for Stripe review. Run `php artisan accounts:stripe-readiness` before submitting its production URL to Stripe. See [docs/stripe-website-readiness.md](docs/stripe-website-readiness.md).

Pricing is published at `/pricing`, shared billing information at `/billing`, and the product catalog is maintained in `config/products.php`. See [docs/commerce.md](docs/commerce.md).

## Development

```bash
composer install
php artisan migrate
php artisan test
vendor/bin/pint --test
```

The local service root returns its readiness state. Laravel also exposes `/up` for health checks.

## Project records

- [Project brief](BRIEF.md)
- [Non-goals](DO-NOT-BUILD.md)
- [Owned differences from Laravel](OWNED-DIFF.md)
- [Central service decision](docs/decisions/ADR-001-central-account-service.md)
- [Client package decision](docs/decisions/ADR-002-reusable-accounts-client.md)
- [Domain glossary](docs/glossary.md)
- [Workflows and state](docs/workflows.md)
- [Stripe setup](docs/stripe.md)
- [Stripe website readiness](docs/stripe-website-readiness.md)
- [Product catalog, pricing, and billing](docs/commerce.md)
- [Public commerce interface patterns](docs/ui-patterns.md)
- [Delivery tickets](docs/tickets.md)
- [Machine-readable project memory](docs/project-memory/project.json)
