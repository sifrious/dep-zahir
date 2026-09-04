# Account identity and lifecycle operations

Product callers may link or unlink identities only for the opaque account established by their current authenticated product session. A fresh provider-neutral assertion is required for linking. Never use email as an identity key.

The last identity remains protected until an approved recovery process exists. Only a caller with `can_manage_account_lifecycle` may provide an accepted recovery reference, record an external identity revocation/recovery, or suspend/reactivate an account. This capability defaults to false and must not be enabled in production until the accountable administrative authority is selected.

For collision responses, investigate through hashed audit provenance; never disclose the owning account. Suspension and reactivation require a reason and are recorded in `account_lifecycle_events`. Product authorization remains separate: a suspended account is denied by entitlement evaluation regardless of product-local roles.

Record a provider-reported revocation with `POST /api/v1/accounts/{account}/identities/revocation`, using only `provider`, `provider_subject`, and a stable `reason_code`. Accept recovery with `DELETE` on the same path only after the provider has completed recovery and identity verification; include the provider-neutral `accepted_recovery_reference`. Zahir stores subject and recovery-reference hashes in `external_identity_lifecycle_events`, never provider credentials or recovery secrets.

Both operations are retry-safe and return `replayed`. A revoked connection remains attached to its original opaque account, so resolution returns `provider_revoked` rather than creating another account. After accepted recovery, require a fresh authentication before a product creates a local session.
