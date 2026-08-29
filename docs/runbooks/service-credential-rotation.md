# Service credential rotation and emergency revocation

Zahir authenticates products with identifiable credentials. Only password hashes are stored; the bearer credential is shown once when issued. Every authenticated API response carries a request ID, and the caller/credential/route/result are recorded without request bodies or tokens.

## Routine rotation

1. Issue a second credential while the existing credential remains valid: `php artisan zahir:caller-credential:issue logres --label=2026-q3`.
2. Put the displayed value into the product's secret store. Do not paste it into tickets, chat, logs, or source control.
3. deploy/restart the product and verify successful Zahir requests under the new credential ID in `service_request_events`.
4. Revoke the old credential: `php artisan zahir:caller-credential:revoke <credential-ulid>`.
5. Confirm the old credential receives HTTP 401 and the new credential still succeeds.

An optional `--expires="2026-09-01T00:00:00Z"` bounds overlap. Keep overlap only as long as rollback requires.

## Emergency revocation

Revoke the exposed credential immediately with the revoke command. If caller-wide compromise is suspected, set `service_callers.disabled_at`; every credential for that caller then fails closed. Issue replacement credentials only after the consumer and secret channel are trusted. Review `service_request_events` and account-resolution events by credential, caller, request ID, and time window.

Production issuance and injection require deployment secret-store access; local and CI tests generate disposable credentials.
