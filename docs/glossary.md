# Glossary

- **Global account:** opaque, durable Zahir identity (`acc_…`).
- **External identity:** unique `(provider, provider_subject)` mapping to one account.
- **VerifiedExternal:** provider-neutral representation of an assertion already verified by an integration adapter.
- **Safe claims:** mutable email, email-verification status, and name metadata; never identity keys.
- **Product entitlement:** time-bounded named grant evaluated by Zahir.
- **Product projection:** application-local user record referencing a Zahir account without sharing storage.
- **Product-local identity:** a product-owned user and session keyed by a Zahir global account; never the global account itself.
- **Runner enrollment identity:** the product-domain record of who enrolled a runner; it grants no execution permission by itself.
- **Execution authorization:** a separate, explicit decision permitting a defined execution operation.
- **Repository/workspace grant:** scoped product-domain authorization that is never inferred from authentication or entitlement alone.
