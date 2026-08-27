<?php

namespace App\Stripe\Contracts;

interface StripeGateway
{
    public function createCustomer(string $accountId, ?string $displayName): string;

    public function createCheckoutSession(
        string $customerId,
        string $accountId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string;

    public function createBillingPortalSession(string $customerId, string $returnUrl): string;
}
