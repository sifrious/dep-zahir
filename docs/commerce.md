# Product catalog, pricing, and billing

Accounts is the public commerce authority for connected products. The configuration in `config/products.php` feeds the public home, product detail pages, `/pricing`, `/billing`, Stripe website readiness, and the future authenticated checkout API.

## Add a product

Add one top-level product entry with:

- stable product key;
- public name, summary, description, and digital-delivery statement;
- one or more named plans;
- displayed price, full currency code, and billing interval;
- Stripe Price ID;
- entitlement granted by that plan;
- concrete included capabilities.

Every plan appears automatically on the pricing and billing pages. The Stripe readiness command checks every configured plan, so an incomplete new product blocks a ready result instead of silently disappearing from the public disclosures.

## Current storage decision

The first product catalog remains configuration-backed. A plan database and administration UI would add state, migrations, authorization, and publishing workflows before a second product needs runtime price management.

Revisit this decision when a second product needs plan changes without a deployment, localized prices, grandfathered plans, or scheduled price publication.

## Policy publication

Standard draft content lives in `resources/policies`. Draft previews are available only in local and test environments. Production `/legal/{document}` routes return content only after the corresponding publication environment flag is enabled.

Before publication, replace every decision marker, set the business and policy metadata, review the complete rendered pages, and obtain whatever legal review the business requires.
