# Zahir

> **License:** Copyright © 2026 Sifrious. All rights reserved. This is
> publicly viewable proprietary software, not open-source software. See
> [LICENSE.md](LICENSE.md).

Zahir is the provider-neutral global account and product-entitlement boundary shared by portfolio applications.

```text
external provider -> verified assertion -> Zahir account -> entitlement
-> product adapter -> product-local user/session -> product authorization
```

Zahir owns opaque account IDs, external identity links, lifecycle status, products, entitlements, resolution decisions, and audit provenance. It never stores credentials, provider sessions, payment instruments, or product profiles. Products consume authenticated HTTP contracts and never query Zahir storage.

## Contracts

- `POST /api/v1/accounts/resolve` accepts a provider-neutral `VerifiedExternal` representation.
- `POST /api/v1/entitlements/decide` returns a deterministic allow/deny decision.
- Both require a service bearer credential configured as `ZAHIR_SERVICE_TOKENS=logres:secret`.

WorkOS AuthKit is provider #1. Its types remain under `App\Identity\WorkOs`; public endpoints do not expose WorkOS objects.

## Verification

```bash
composer install
composer test
```

See [the domain contract](docs/zahir-contract.md) and [the WorkOS decision](docs/decisions/ADR-003-workos-authkit.md).
