# Burdgen entitlement bootstrap

## Contract identifiers

| Field | Value |
|---|---|
| Product key | `burdgen` |
| Product name | `Burdgen` |
| Entitlement | `access` |
| Written as | `burdgen.access` |
| Contract version | `mme-2102-v1` |
| Development account | `acc_01j6g000000000000000000001` |
| Service caller key | `burdgen` |

These are stable. Burdgen names `burdgen` as its `ZAHIR_PRODUCT` and `access` as
its `ZAHIR_ACCESS_ENTITLEMENT`; a change here is a coordinated contract change
across Zahir and every consuming deployment, not a rename.

The pair deliberately mirrors `logres.access` in shape and shares nothing else.
A Logres grant never opens Burdgen and a Burdgen grant never opens Logres, which
`BurdgenProductEntitlementTest` asserts in both directions — if that ever passed
by accident, entitlements would be decoration.

## Provisioning

`BurdgenProductSeeder` provisions the product idempotently, through the shared
`ProductSeeder` base that Logres also uses, so a second consumer cannot drift
into subtly different semantics. Re-running it refreshes rather than duplicates.

```bash
php artisan db:seed --class="Database\\Seeders\\BurdgenProductSeeder"
```

`DatabaseSeeder` provisions both products.

## Development and test grants

Opt in with `ZAHIR_SEED_DEVELOPMENT_GRANTS=true`. That grants
`acc_01j6g000000000000000000001` the `burdgen.access` entitlement — a
deterministic account distinct from the Logres fixture account, so neither
product's fixture can mask a bug in the other.

Never enable this in production.

## Production access

Production is deny-by-default: `deny_until_launch_policy_approved`. No grant is
seeded, and there is no grant-creation API. The accountable role is
`launch_access_administrator` and grants reconcile to the
`manual_invitation_registry`.

The launch access policy itself is MME-2097 and is not decided here. Until it is,
an authenticated Burdgen user with no grant correctly receives
`unauthorized_product`.

## Issuing Burdgen's service credential

```bash
php artisan zahir:caller-credential:issue burdgen
```

Shown once, stored only as a hash, and it goes in Burdgen's deployment secret
store as `ZAHIR_SERVICE_TOKEN`. Account-lifecycle authority is a separate
capability and stays off for a product caller: Burdgen may ask about access, not
change it.

Rotate with a second overlapping issue followed by
`zahir:caller-credential:revoke` on the old credential.

## What Zahir will not accept from Burdgen

Zahir decides on grants alone. It holds no notion of Burdgen's local roles,
preferences, onboarding progress, projects, or GitHub connection, so none of them
can be argued into an entitlement. Product-local state cannot elevate global
access — that is the boundary, and it is why the local projection is safe to keep
thin.

No GitHub token, installation, repository reference, or scope enters Zahir's
contract. See ADR-004.
