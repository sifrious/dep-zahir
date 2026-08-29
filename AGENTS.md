# Zahir project instructions

- Read `BRIEF.md`, `DO-NOT-BUILD.md`, and `docs/zahir-contract.md` before changing scope or architecture.
- Zahir owns opaque global accounts, external identity links, lifecycle state, products, entitlements, resolution, and audit provenance.
- External providers own credentials, sessions, MFA, recovery, and identity verification.
- Products own HTTP/OIDC transport, local users and sessions, authorization, preferences, onboarding, and UI.
- Keep provider-specific types under integration adapters. Public contracts remain provider-neutral.
- Identify external identities only by `(provider, provider_subject)`; email is mutable metadata and never an identity key.
- Products use authenticated contracts and never connect directly to Zahir storage.
