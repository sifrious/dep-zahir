# Stripe website readiness

Stripe does not require an advertising campaign. It requires a functioning, publicly accessible business website that accurately identifies the business and explains the products or services offered.

Accounts provides the public product catalog and trust center for that review. Before submitting its production URL to Stripe, run:

```bash
php artisan accounts:stripe-readiness
```

The command fails until all required factual and approved policy fields are present.

## Required factual configuration

```dotenv
BUSINESS_NAME=
BUSINESS_SUPPORT_EMAIL=
BUSINESS_SUPPORT_PHONE=
BUSINESS_ADDRESS=
BUSINESS_LOGRES_PAID_PRICE=
BUSINESS_LOGRES_PAID_CURRENCY=USD
BUSINESS_LOGRES_PAID_BILLING_PERIOD=month
```

Every product uses the `BUSINESS_<PRODUCT>_PAID_PRICE`, `BUSINESS_<PRODUCT>_PAID_CURRENCY`, `BUSINESS_<PRODUCT>_PAID_BILLING_PERIOD`, and `STRIPE_<PRODUCT>_PAID_PRICE_ID` pattern shown in `.env.example`. Free tiers are displayed as free, require no payment method, and are excluded from Stripe Price-ID readiness checks. Every paid tier blocks readiness until its real public price and Stripe Price ID are configured.

The displayed business name and product descriptions must match the business profile submitted to Stripe. Publish at least two discoverable customer-service methods. Display the full purchase currency, not only a currency symbol.

## Required approved documents

- Privacy Policy
- Terms of Service
- Refund and Dispute Policy
- Subscription Cancellation Policy
- Digital Service Delivery Policy

The trust registry also tracks acceptable use, cookies, data processing, security, subprocessors, accessibility, retention, and vulnerability disclosure. These remain unpublished until their real content, version, and effective date are approved.

## Production checks

- The production site loads without authentication or regional blocking.
- Every page uses HTTPS.
- The root page identifies the business and links to every product being sold.
- Every purchasable product displays its description, price, currency, billing interval, and delivery method.
- Customer support and required policies are reachable from the product page.
- Promotions and trials disclose their complete terms before purchase.
- Stripe-hosted Checkout is used so Accounts does not collect complete card numbers.
- The URL in the Stripe business profile matches the deployed Accounts site.
