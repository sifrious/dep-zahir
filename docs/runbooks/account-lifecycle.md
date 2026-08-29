# Account identity and lifecycle operations

Product callers may link or unlink identities only for the opaque account established by their current authenticated product session. A fresh provider-neutral assertion is required for linking. Never use email as an identity key.

The last identity remains protected until an approved recovery process exists. Only a caller with `can_manage_account_lifecycle` may provide an accepted recovery reference or suspend/reactivate an account. This capability defaults to false and must not be enabled in production until the accountable administrative authority is selected.

For collision responses, investigate through hashed audit provenance; never disclose the owning account. Suspension and reactivation require a reason and are recorded in `account_lifecycle_events`. Product authorization remains separate: a suspended account is denied by entitlement evaluation regardless of product-local roles.
