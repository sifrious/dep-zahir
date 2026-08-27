# Public commerce interface patterns

## Pricing catalog

The pricing catalog presents every configured product followed by its plans. It composes Burd page headers, cards, badges, and buttons rather than introducing an Accounts-specific pricing component.

### States

| State | Presentation | Behavior |
|---|---|---|
| Published price | Amount, ISO currency code, and billing interval | Links to product and billing details |
| Missing price | Neutral `Price pending publication` badge | No purchase action is rendered |
| Additional product | New labeled section and plan-card grid | Appears automatically from `config/products.php` |

### Accessibility

- One page-level heading identifies Pricing.
- Each product is a labeled section with a level-two heading.
- Each plan is a titled card with a correctly nested heading.
- Currency uses the full configured code instead of relying on a symbol.
- Missing commercial data is visible text, not color alone.

## Billing overview

The billing overview combines four titled cards for subscriptions, payments, invoices, and taxes with a record list of all product plans. A record list is used because the reader chooses a product-plan record; a comparison table is unnecessary until multiple plan attributes must be compared down columns.

### States

| State | Presentation | Behavior |
|---|---|---|
| Anonymous | General billing disclosures and published plans | No portal action |
| Authenticated, future | Same disclosures plus Stripe Billing Portal action | Opens a short-lived Stripe-hosted portal URL |
| Support configured | Direct billing-support action | Uses the published support address |

## Policy document

Published policies use a Burd page header and readable card-width article. Local draft previews add a warning alert and replace missing business decisions with explicit markers.

### Accessibility and safety

- Draft status is stated in text and a status alert.
- Draft routes return 404 outside local and test environments.
- Published metadata includes version and effective date.
- Markdown headings remain nested below the page title.
