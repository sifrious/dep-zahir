# mary.win entitlement bootstrap

## Contract identifiers

| Field | Value |
|---|---|
| Product key | `mary-win` |
| Product name | `mary.win` |
| Entitlement | `access` |
| Written as | `mary-win.access` |
| Contract version | `mary-win-v1` |
| Development account | `acc_01j6g000000000000000000002` |
| Service caller key | `mary-win` |

Provisioned by `MaryWinProductSeeder` through the shared `ProductSeeder`, the
same path Logres and Burdgen take. Re-running refreshes rather than duplicates.

```bash
php artisan db:seed --class="Database\\Seeders\\MaryWinProductSeeder"
php artisan zahir:caller-credential:issue mary-win
```

Production is deny-by-default under `deny_until_launch_policy_approved`, and the
launch access policy remains MME-2097's to decide.

## Why a personal site gets its own product

Because the alternative is worse. Reusing another product's entitlement would
mean a Logres grant silently opening a personal site's admin area, which is the
exact coupling entitlements exist to prevent. `BurdgenProductEntitlementTest`
now asserts all nine combinations across the three products: an account reaches
the product it holds a grant for, and nothing else.

Only the authenticated area of mary.win sits behind this. The public site —
portfolio, writing, contact — is served without any account at all, and must
stay that way.
