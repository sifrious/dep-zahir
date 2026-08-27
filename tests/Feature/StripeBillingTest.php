<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Stripe\Contracts\StripeGateway;
use App\Stripe\StripeBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_reuses_one_stripe_customer_for_an_account(): void
    {
        $gateway = new class implements StripeGateway
        {
            public int $customersCreated = 0;

            public function createCustomer(string $accountId, ?string $displayName): string
            {
                $this->customersCreated++;

                return 'cus_test';
            }

            public function createCheckoutSession(
                string $customerId,
                string $accountId,
                string $priceId,
                string $successUrl,
                string $cancelUrl,
            ): string {
                return 'https://checkout.stripe.test/session';
            }

            public function createBillingPortalSession(string $customerId, string $returnUrl): string
            {
                return 'https://billing.stripe.test/session';
            }
        };

        $billing = new StripeBilling($gateway);
        $account = Account::query()->create(['display_name' => 'Person']);

        $first = $billing->checkout($account, 'price_logres', 'https://app.test/success', 'https://app.test/cancel');
        $second = $billing->checkout($account, 'price_logres', 'https://app.test/success', 'https://app.test/cancel');

        $this->assertSame('https://checkout.stripe.test/session', $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $gateway->customersCreated);
        $this->assertSame('cus_test', $account->stripeCustomer()->firstOrFail()->customer_id);
    }
}
