# Logres entitlement bootstrap

The stable product key is `logres` and its initial entitlement is `access`. `LogresProductSeeder` provisions the product idempotently through the shared `ProductSeeder` base, which every product uses so a second consumer cannot drift into different semantics. Development and test environments may opt into one deterministic grant with `ZAHIR_SEED_DEVELOPMENT_GRANTS=true`.

Production remains deny-by-default until the launch access policy is approved. The accountable role is `launch_access_administrator`; grants and revocations must be reconciled to the `manual_invitation_registry`. Zahir stores only the grant source and opaque source reference. Payment instruments, billing records, and payment-provider payloads do not enter Zahir.

After the launch policy is approved, add grants through an authenticated administrative workflow, retain the external source reference, and revoke them by setting `revoked_at`. Do not enable the development seed in production.

Burdgen is provisioned the same way under its own identifiers; see [the Burdgen entitlement bootstrap](burdgen-entitlement-bootstrap.md). The two grants are independent in both directions.
