# ADR-005: Where account email and notifications belong

Status: **proposed** — needs an accountable decision before any of it is built

## Context

Adopting shared authentication in mary.win removed a complete local email stack:
registration confirmation, address verification, and password reset. The obvious
question is where that work went, and whether Zahir should absorb it now that it
is the one place that knows who someone is across products.

"Signups, confirmations, logging, and notifications" is four different concerns
wearing one coat. They do not have the same answer.

## Decision (proposed)

Split them by who owns the thing being described.

### Identity email stays with the provider — not Zahir

Address verification, password reset, magic links, and MFA codes belong to the
external identity provider. ADR-003 already assigns it there, and `DO-NOT-BUILD`
forbids credential and recovery storage.

This is not a boundary of taste. Sending a verification email means owning the
token being verified, and sending a reset means owning the credential being
reset. Zahir cannot do either without becoming the credential store it exists
not to be. Products must not do it either: mary.win's local verification was
asserting something it had no way to check.

### Product notifications stay with the product

A finished run, a new comment, a weekly digest. Zahir has no idea what those
are, and giving it one would mean teaching it every product's domain.

### Marketing and waitlist signup is not authentication at all

Burdgen's preview waitlist and a personal site's contact form collect addresses
from people who have no account and may never have one. Putting those in Zahir
would create email rows attached to no account, in the one service whose whole
contract is that email is never identity. They belong to the product, or to a
marketing tool.

### Account-lifecycle notification is the part that genuinely belongs to Zahir

Suspension, reactivation, an identity linked or unlinked, product access granted
or revoked. Zahir owns these events, already audits them, and is the only party
that sees them across every product. Telling someone their account was suspended
once, rather than once per product — or not at all — is a real gap.

This is the piece worth building. It is also a genuine expansion: Zahir today has
no mail configuration, no queue, and no notification surface, and its brief is
deliberately small. It should be a ticket with its own acceptance criteria, not
something that arrives as a side effect of an authentication change.

### Logging is already correct and should not move

Zahir logs what it decides — service requests, authentication outcomes, lifecycle
transitions — with provider subjects hashed and no tokens or emails. Products log
what they render, under their own prefixes. Nothing here needs consolidating; a
combined log would mean Zahir receiving product context it has no business
holding.

## The smaller thing that would serve most of this

Zahir already stores `email` as an allowlisted claim, refreshed on every
resolution, so it already holds the authoritative address. What it lacks is a way
to *ask* for it: there is no "contact address for this account" in the public
contract, and no stated rule for which identity wins when an account has several.

Exposing that — one nullable field on the account contract plus a documented
precedence rule — is a much smaller change than a notification subsystem, and it
unblocks any product or operator that needs to reach a person. It does not make
email identity: resolution stays keyed on `(provider, provider_subject)`, equal
emails still never merge, and the field stays mutable metadata.

That is the recommended first step, and it can be decided independently of
whether Zahir ever sends mail.

## Consequences if accepted

- No product rebuilds local verification or password reset. mary.win's removal is
  the pattern, not an exception.
- Zahir gains a contact-address read on the account contract, and nothing else
  until lifecycle notification is separately accepted.
- A product that needs to email its own users keeps doing so with its own mailer
  and its own templates.

## Open questions for the decision

1. Which identity supplies the contact address when an account has several —
   most recently authenticated, first linked, or an explicit primary?
2. Should lifecycle notification be Zahir sending mail, or Zahir emitting an
   event that products and operators subscribe to? The second keeps the mail
   stack out of Zahir entirely.
3. Does a suspension notice come from Zahir or from the product that noticed?
   Sending from both is the failure mode.
