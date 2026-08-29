# Release, backup, restore, and rollback

No production migration starts without a completed database backup, a recorded immutable backup reference, successful provider health checks, and an identified rollback operator. Record the exact service, accounts-client, and Logres-host commits with `bin/release-manifest`; attach its output to the release record.

## Before migration

1. Confirm the release manifest matches the deployed artifacts and repository tags.
2. Put Zahir into the deployment's write-drain or maintenance procedure.
3. Create a database-native encrypted backup and record its immutable reference, creation time, database version, size, checksum, and retention expiry.
4. Verify the backup is readable from the isolated restore environment. A backup that has not restored successfully is not a valid prerequisite.
5. Run migrations with the release operator watching errors and latency.

## Rehearsal and restore verification

CI builds a populated SQLite database, copies it as a backup, exercises migration rollback and forward migration, restores the copied database, and verifies accounts, external identity mappings, entitlement grants, resolution provenance, lifecycle audit, and service audit counts. Production rehearsal uses the same assertions against a temporary database restored with the production engine's native tools.

After every restore, verify schema version, row counts, foreign-key integrity, one known opaque account-to-identity resolution, one allowed entitlement, one denied entitlement, and audit provenance timestamps. Never log provider subjects or claims while sampling.

## Rollback

If application rollback is compatible with the migrated schema, redeploy the prior manifest and retain the forward-compatible schema. If the schema must roll back, stop writes, run only rehearsed down migrations, and verify counts/contracts. If either path is unsafe, restore the pre-migration backup into a new database, verify it, then atomically repoint the service. Preserve the failed database for investigation.

Rollback completion requires health/readiness, account resolution, entitlement decision, caller authentication, and Logres fail-closed checks before writes resume.
