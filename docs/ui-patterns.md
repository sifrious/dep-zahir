# Public commerce interface patterns

## Product behavior reference

Each configured product can expose a public behavior reference from its product page. The reference composes Burd page headers, badges, cards, buttons, and semantic lists. It separates current availability from the documented contract so planned behavior cannot be mistaken for a live capability.

### Sections

- Availability and documentation status
- Required inputs and resulting outputs
- Ordered request-to-result workflow
- Caller interactions and interchangeable execution agents
- Product, account, payment, identity, and execution-target data boundaries
- Current limitations and unresolved implementation decisions

### Accessibility

- One page-level heading names the product behavior reference.
- Workflow order is represented by an ordered list rather than visual numbering alone.
- Status and limitations are visible text and do not rely on color.
- Section headings are supplied by Burd cards with consistent nesting.
- Navigation actions use links because they move to other documents.

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
