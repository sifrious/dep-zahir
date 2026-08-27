# Stripe setup

Accounts uses Stripe-hosted Checkout and Billing Portal sessions. Stripe owns payment instruments, subscriptions, invoices, and refunds. Accounts stores only Stripe object identifiers, verified event metadata, and the resulting product entitlements.

## Local test configuration

1. Create or select a Stripe test-mode account.
2. Create a Logres product with a recurring Price in Stripe.
3. Copy the test secret key and recurring Price ID.
4. Install and authenticate the Stripe CLI.
5. Forward Stripe events to Accounts:

```bash
stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhooks
```

6. Copy the displayed webhook signing secret into the local environment:

```dotenv
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_LOGRES_PRICE_ID=price_...
```

7. Clear cached configuration and run Accounts:

```bash
php artisan config:clear
php artisan serve
```

The webhook processes `customer.subscription.created`, `customer.subscription.updated`, and `customer.subscription.deleted`. Active and trialing subscriptions grant the entitlement mapped to the configured Price. Other subscription states revoke the subscription's grant.

## Production configuration

1. Create the live Stripe Product and recurring Price.
2. Set the live secret key and Price ID as Laravel Cloud secrets.
3. Register `https://<accounts-domain>/api/stripe/webhooks` as a Stripe webhook destination.
4. Subscribe the destination to the three subscription events listed above.
5. Store that destination's signing secret as `STRIPE_WEBHOOK_SECRET`.
6. Send a live-mode test event and verify that Accounts records it before accepting purchases.

Never commit Stripe secrets or copy them into connected product applications. Checkout and portal routes will be exposed only after service authentication is selected.
