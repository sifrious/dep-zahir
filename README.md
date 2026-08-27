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

The external identity provider and service-authentication protocol remain manual launch decisions. No public account-resolution or entitlement endpoint is exposed until those decisions are accepted.

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
- [Delivery tickets](docs/tickets.md)
- [Machine-readable project memory](docs/project-memory/project.json)
