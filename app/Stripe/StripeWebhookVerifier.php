<?php

namespace App\Stripe;

use Stripe\Event;
use Stripe\Webhook;

final readonly class StripeWebhookVerifier
{
    public function __construct(private string $secret) {}

    public function verify(string $payload, string $signature): Event
    {
        return Webhook::constructEvent($payload, $signature, $this->secret);
    }
}
