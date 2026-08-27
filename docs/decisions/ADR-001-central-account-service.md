# ADR-001: Central account service instead of a shared user database

**Status:** Accepted  
**Date:** 2026-08-27  
**Tickets:** ACC-001, ACC-002, ACC-003  
**Reversibility:** B  
**Store relation:** feeds and consumes the authoritative account store

## Context

Logres and future products need one stable account identity, external authentication, and centrally interpreted product access. Direct database sharing would couple every product to one schema, migration sequence, and database availability boundary. External identity and payment providers should retain credentials and payment instruments, while internal product identity must remain portable across providers.

## Options

### A: Shared account database

Each application reads and writes common account tables directly. Initial implementation is short, but schema changes, deployments, credentials, and outage behavior become coupled across products.

### B: Central account service

One service owns account and entitlement persistence. Products consume signed identity claims and versioned service contracts. This adds one deployed service but preserves application and provider boundaries.

## Decision

Use a central account service. The load-bearing reason is that products require one identity without acquiring shared-schema ownership. External providers remain responsible for authentication and payment; Accounts owns stable internal identity and entitlements.

## Consequences

- Easier: single sign-on, provider replacement, cross-product entitlements, centralized suspension, and independent product schemas.
- Harder: Accounts becomes a production dependency and needs explicit outage, caching, audit, and service-authentication behavior.
- Revisit trigger: the selected identity platform proves it can expose portable internal account IDs and product entitlements to multiple applications with lower exit and operating cost than this service.

