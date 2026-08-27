<?php

namespace App\Stripe;

use App\Models\Account;
use App\Models\StripeCustomer;
use App\Stripe\Contracts\StripeGateway;

final readonly class StripeBilling
{
    public function __construct(private StripeGateway $gateway) {}

    public function checkout(
        Account $account,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $customer = $this->customerFor($account);

        return $this->gateway->createCheckoutSession(
            $customer->customer_id,
            $account->id,
            $priceId,
            $successUrl,
            $cancelUrl,
        );
    }

    public function portal(Account $account, string $returnUrl): string
    {
        return $this->gateway->createBillingPortalSession(
            $this->customerFor($account)->customer_id,
            $returnUrl,
        );
    }

    private function customerFor(Account $account): StripeCustomer
    {
        $existing = $account->stripeCustomer()->first();

        if ($existing !== null) {
            return $existing;
        }

        return $account->stripeCustomer()->create([
            'customer_id' => $this->gateway->createCustomer($account->id, $account->display_name),
        ]);
    }
}
