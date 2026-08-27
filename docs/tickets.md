# Accounts launch tickets

## Release predicate

Accounts is live when a production identity can resolve to one stable account, Stripe can establish paid access through authenticated webhook state, and Logres can receive an authenticated decision for `logres.access` without direct database access.

| ID | Ticket | Acceptance evidence | Gate | Status |
|---|---|---|---|---|
| ACC-001 | Establish project genesis | Brief, owned-diff register, ADR, glossary, workflows, and project memory exist | None | Complete |
| ACC-002 | Scaffold framework-default Laravel service | Default application boots and the default test suite passes | None | Complete |
| ACC-003 | Persist stable accounts and external identities | One issuer/subject pair resolves idempotently to one account; conflicts are rejected | None | Complete |
| ACC-004 | Persist products and entitlements | `logres.access` can be granted, expired, revoked, and evaluated deterministically | None | Complete |
| ACC-005 | Define service authentication | Unauthenticated product requests are rejected and authenticated Logres requests are attributed | Identity protocol decision | Decision required |
| ACC-006 | Expose account resolution | A verified external identity resolves through a versioned endpoint | Identity provider decision | Blocked by ACC-008 |
| ACC-007 | Expose entitlement decisions | Logres receives allowed or denied with account, entitlement, and evaluation time | ACC-005 | Ready after ACC-005 |
| ACC-008 | Select the external identity provider | Decision records protocol, browser flow, NativePHP compatibility, export path, and operating cost | Manual approval | Decision required |
| ACC-009 | Implement the selected identity adapter | Real provider login creates or resolves a production account | ACC-008 | Pending |
| ACC-010 | Integrate Logres | Logres signs in through Accounts and enforces `logres.access` | ACC-009, ACC-016 | Pending |
| ACC-011 | Deploy Accounts to Laravel Cloud | Production health, database, secrets, logs, migrations, and rollback are verified | Domain and Cloud authority | Pending |
| ACC-012 | Prove the launch path | A production identity signs in and reaches the authorized Logres request screen | ACC-010, ACC-011 | Pending |
| ACC-013 | Select Stripe as the payment provider | Stripe owns checkout, payment instruments, subscriptions, invoices, refunds, and the customer billing portal | User decision | Complete |
| ACC-014 | Implement Stripe-derived entitlements | Signed idempotent Stripe events alter named grants without exposing payment instruments | ACC-013 | Complete |
| ACC-015 | Publish the public trust center | Stable public URLs list approved legal and compliance documents with version and effective date | Approved document copy | In progress |
| ACC-016 | Build the reusable Accounts client package | Laravel applications can initiate login and call authenticated Accounts APIs without provider-specific application code | ACC-005, ACC-008 | Scaffolded; integrations pending |
| ACC-017 | Integrate the Burd design system | Public account and trust surfaces consume the standalone Burd package | Publish v0.1.0 for Laravel Cloud | Complete locally; production publication pending |
| ACC-018 | Implement Stripe checkout and billing portal handoffs | Accounts creates Stripe-hosted sessions and returns URLs without handling payment instruments | ACC-005, product and price decision | Services complete; authenticated routes pending |
| ACC-019 | Reconcile Stripe commercial state | Accounts can repair missed webhook state from Stripe without duplicating subscription policy in products | ACC-014 | Pending |
| ACC-020 | Publish the Stripe-verifiable business and product site | The public root identifies the business, describes every sold product, displays price and currency, exposes support details, and links required approved policies | Business facts and approved policy copy | Structure complete; content decisions required |
| ACC-021 | Centralize product pricing and billing pages | Every configured product automatically appears on product, pricing, billing, and Stripe-readiness surfaces | Real product and plan facts | Complete for configuration-backed MVP |
| ACC-022 | Approve standard commerce and legal drafts | Privacy, terms, refunds, cancellations, and delivery contain no decision markers and publish with version and effective date | Business and legal decisions | Decision required |
| ACC-023 | Publish product behavior references | Every documented product has a public, status-labeled reference for purpose, inputs, workflow, outputs, interactions, data boundaries, entitlements, and limits | Accurate product contract | Complete for Logres planned MVP |
