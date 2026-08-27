# Accounts project instructions

- Read `BRIEF.md`, `DO-NOT-BUILD.md`, and `docs/project-memory/project.json` before changing scope or architecture.
- External identity providers own credentials, sessions, multifactor authentication, and recovery.
- External payment providers own payment instruments, checkout, charges, subscriptions, invoices, and refunds.
- Accounts owns stable account identities, external identity links, products, and entitlement grants.
- Product applications consume authenticated Accounts contracts and never connect directly to its database.
- Keep provider-specific types behind integration adapters.
- Do not expose account-resolution or entitlement endpoints until caller authentication is selected and recorded.
- Update project memory and current documentation in the same change as behavior.
- Record every intentional deviation from the Laravel scaffold in `OWNED-DIFF.md`.
