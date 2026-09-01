# ADR-004: Burdgen uses Zahir authentication plus a separately linked GitHub integration

Status: accepted for the v1 contract

## Decision

Burdgen v1 uses the shared Zahir consumer seam for global account authentication and product access, then links GitHub as a separate repository-provider integration. This is Path B from MME-1925.

The selected external identity provider remains WorkOS AuthKit (ADR-003). A GitHub identity may also happen to be used upstream by WorkOS, but that does not turn its username or GitHub subject into the Zahir account ID and does not supply repository authorization to Burdgen.

## Identities and owners

- Zahir owns the opaque `acc_*` account and verified external identity mappings.
- Burdgen owns its local account projection, session, onboarding continuation, and conceptual project.
- The GitHub integration owns the GitHub subject, installation/grant, token lifecycle, repository permissions, and repository references.
- GitHub usernames and emails are mutable display metadata, never account keys.

The product-facing association is versioned and refers to `account_ref`, `integration_ref`, GitHub `subject_ref`, authorization state, permission summary, and grant provenance. Credentials and raw provider objects are excluded.

## Replay and recovery

Account resolution remains idempotent by Zahir's `(provider, provider_subject)` contract. GitHub callback completion is idempotent by a single-use callback receipt plus the stable GitHub subject/installation identity. If that identity is already linked to the same account, the grant metadata is refreshed. If it belongs to a different account, linking fails closed and requires an explicit account-recovery operation.

Burdgen stores an opaque continuation ref before redirect. Successful reauthorization resumes the exact semantic step and originating intent/plan. Cancellation, expired state, or provider failure preserves that continuation for retry.

Revoking GitHub changes only the integration authorization state. It never deletes or suspends the Zahir account, Burdgen project, conversations, Twinkles, or plans.

## Minimum GitHub permissions

V1 requests only the permissions needed for the selected path and presents them before grant:

- identity/read metadata needed to resolve the GitHub subject;
- repository metadata/content access for repositories the user explicitly makes available;
- repository administration/create permission only when the user selects repository creation.

Prefer a GitHub App installation with repository selection over a broad OAuth `repo` grant. Repository creation may require a distinct, just-in-time authorization path because installation access and user-to-server creation authority are not equivalent. Organization policy denial, repository not granted to an installation, private-repository omission, insufficient creation permission, revoked authorization, and temporary GitHub unavailability are separate reason codes; none is reported as global authentication failure.

## Current implementation gap

The present Burdgen cloud flow signs in directly with GitHub and stores the GitHub subject/token on its local `users` row. That is a reference implementation to replace, not the v1 target contract. Migration must wait for the reusable Zahir consumer and conformance seam rather than reproducing Zahir behavior inside Burdgen.

## Consequences

- MME-1906 consumes an authorized GitHub integration ref, not a login session.
- A user can remain authenticated and deliberate while GitHub is revoked.
- GitHub reauthorization is a recoverable onboarding transition.
- No Zahir public contract gains GitHub token or repository fields.
