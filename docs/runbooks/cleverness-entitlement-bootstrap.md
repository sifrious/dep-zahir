# Cleverness entitlement bootstrap

## Contract identifiers

| Field | Value |
|---|---|
| Product key | `cleverness` |
| Product name | `Cleverness` |
| Entitlement | `access` |
| Written as | `cleverness.access` |
| Contract version | `cleverness-v1` |
| Development account | `acc_01j6g000000000000000000003` |
| Service caller key | `cleverness` |

```bash
php artisan db:seed --class="Database\\Seeders\\ClevernessProductSeeder"
php artisan zahir:caller-credential:issue cleverness
```

## What this replaced

Cleverness gated `/admin` with one shared password in `ADMIN_PASSWORD`, held in
the session as a boolean. That has no notion of who is signed in: it cannot be
revoked for one person, cannot be audited, cannot be rotated without telling
everyone who has it, and leaks permanently the first time it is pasted anywhere.

A named account with a grant fixes all four. Revocation is per person and takes
effect within one decision window, every access decision is attributable, and
there is no shared secret left to leak.

## Its own product, not mary.win's

Cleverness is a subdomain of mary.win, but a subdomain is not the same access
decision. Sharing `mary-win.access` would mean a grant for the personal site's
settings silently opening the debt dashboard. They are separate products, and
somebody who should reach both is granted both.

Only `/admin` sits behind this. The public site — analytics, bibliography, seam
map, talks — is served with no account at all.
