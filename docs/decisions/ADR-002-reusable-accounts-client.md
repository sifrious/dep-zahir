# ADR-002: Reusable Accounts client package

**Status:** Accepted  
**Date:** 2026-08-27  
**Tickets:** ACC-016  
**Reversibility:** B  
**Store relation:** consumes Accounts contracts without direct store access

## Context

Logres and future Laravel or NativePHP applications need the same login handoff, verified identity representation, account resolution, and entitlement requests. Repeating that code would couple every application independently to the selected identity provider and service protocol.

## Options

### A: Implement each application independently

Each product owns provider callbacks and Accounts requests. This avoids a package but repeats security-sensitive integration behavior and permits contracts to drift.

### B: Reusable client package with narrow adapters

One package defines provider-independent identity values, a login-driver boundary, and authenticated Accounts API calls. Applications still own product sessions and local projections.

## Decision

Build `sifrious/accounts-client` as a separate Composer package. Keep the selected identity provider behind `LoginDriver` and keep application sessions, product records, credentials, and payment state outside the package.

## Consequences

- Easier: consistent integration, centralized protocol fixes, provider replacement, and reusable tests.
- Harder: package releases must remain compatible with versioned Accounts endpoints.
- Revisit trigger: a second application cannot consume the package without product-specific branches or the selected identity platform supplies an equally portable maintained client.
