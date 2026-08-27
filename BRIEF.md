# Accounts

## Problem

People using Logres and future products need one account, one sign-in identity, and one authoritative answer to which products and capabilities they may use. Today each product would otherwise create its own users, authentication state, and billing interpretation, forcing repeated signup and making cross-product access inconsistent. Accounts changes that experience so a person signs in once, receives a stable account identity, and carries centrally managed product entitlements into every connected application.

## Non-goals

- Accounts does not store passwords, MFA secrets, recovery credentials, or login sessions. Revisit only if no suitable external identity provider can meet the required authentication and export contracts.
- Accounts does not store card numbers or directly execute charges. Revisit only if an external payment processor cannot support the required products or jurisdictions.
- Accounts does not contain Logres requests, tasks, runs, prompts, or artifacts. Revisit only if a concept is demonstrably shared account state rather than product state.
- Accounts does not model a general people-and-relationship graph. Revisit when Bokonon has a second concrete consumer and an accepted identity-to-person contract.
- Accounts does not provide organization administration in the first milestone. Revisit when a second real person must share one paid product account.
- Accounts does not provide a customer billing portal before a paid plan exists. Revisit when the first paid product and its cancellation/refund policy are accepted.

## Owned-diff budget

The project starts from the current Laravel scaffold. Genesis authorizes only the smallest account-service capability: stable accounts, external identity links, products, entitlements, service-facing resolution and authorization endpoints, and the persistence required by those behaviors. External identity and payment providers must enter through provider-specific adapters only after their contracts are selected. Every other framework deviation requires an `OWNED-DIFF.md` entry with an observed trigger.

Accounts is also the public source of truth for approved legal and compliance documents. Connected applications use a reusable Accounts client package for login integration and authenticated service requests instead of embedding provider-specific behavior.

## First measurable milestone

By 2026-09-03, a person can authenticate through the selected external identity provider, Accounts resolves or creates one stable account, Logres can ask whether that account has `logres.access`, and the response is exercised end to end against the production deployment without either application sharing database credentials.

## Kill criteria

- Stop the standalone service if the selected identity platform supplies portable global accounts and product entitlements without coupling products to its proprietary user schema.
- Stop before payment work if no paid product, price, and access consequence have been accepted within 90 days of the first login milestone.
- Collapse Accounts into a simpler boundary if it requires more than four hours per month of operations before a second product consumes it.
- Replace the implementation if a security review finds that it must handle raw credentials or payment instruments to satisfy the milestone.
