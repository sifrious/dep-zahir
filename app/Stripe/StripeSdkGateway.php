<?php

namespace App\Stripe;

use App\Stripe\Contracts\StripeGateway;
use Stripe\StripeClient;
use UnexpectedValueException;

final readonly class StripeSdkGateway implements StripeGateway
{
    public function __construct(private StripeClient $stripe) {}

    public function createCustomer(string $accountId, ?string $displayName): string
    {
        $parameters = [
            'metadata' => ['account_id' => $accountId],
        ];

        if ($displayName !== null) {
            $parameters['name'] = $displayName;
        }

        $customer = $this->stripe->customers->create($parameters);

        return $customer->id;
    }

    public function createCheckoutSession(
        string $customerId,
        string $accountId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $session = $this->stripe->checkout->sessions->create([
            'customer' => $customerId,
            'client_reference_id' => $accountId,
            'line_items' => [['price' => $priceId, 'quantity' => 1]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['account_id' => $accountId],
            'subscription_data' => ['metadata' => ['account_id' => $accountId]],
        ]);

        if (! is_string($session->url)) {
            throw new UnexpectedValueException('Stripe Checkout did not return a URL.');
        }

        return $session->url;
    }

    public function createBillingPortalSession(string $customerId, string $returnUrl): string
    {
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }
}
