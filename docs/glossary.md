# Glossary

- **Global account:** opaque, durable Zahir identity (`acc_…`).
- **External identity:** unique `(provider, provider_subject)` mapping to one account.
- **VerifiedExternal:** provider-neutral representation of an assertion already verified by an integration adapter.
- **Safe claims:** mutable email, email-verification status, and name metadata; never identity keys.
- **Product entitlement:** time-bounded named grant evaluated by Zahir.
- **Product projection:** application-local user record referencing a Zahir account without sharing storage.
