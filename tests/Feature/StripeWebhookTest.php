<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\EntitlementGrant;
use App\Models\StripeCustomer;
use App\Models\StripeEvent;
use App\Stripe\StripeWebhookVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_subscription_event_grants_access_idempotently(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        config()->set('services.stripe.prices.logres.price_id', 'price_logres');
        $this->app->forgetInstance(StripeWebhookVerifier::class);

        $account = Account::query()->create();
        StripeCustomer::query()->create([
            'account_id' => $account->id,
            'customer_id' => 'cus_test',
        ]);

        $payload = $this->subscriptionPayload('evt_active', 'active');
        $signature = $this->signature($payload, 'whsec_test');

        $this->call('POST', '/api/stripe/webhooks', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk()->assertJson(['received' => true]);

        $this->call('POST', '/api/stripe/webhooks', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(1, StripeEvent::query()->count());
        $this->assertSame(1, EntitlementGrant::query()->count());
        $this->assertNull(EntitlementGrant::query()->firstOrFail()->revoked_at);
    }

    public function test_a_canceled_subscription_revokes_its_grant(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        config()->set('services.stripe.prices.logres.price_id', 'price_logres');
        $this->app->forgetInstance(StripeWebhookVerifier::class);

        $account = Account::query()->create();
        StripeCustomer::query()->create([
            'account_id' => $account->id,
            'customer_id' => 'cus_test',
        ]);

        $activePayload = $this->subscriptionPayload('evt_active', 'active');
        $canceledPayload = $this->subscriptionPayload('evt_canceled', 'canceled');

        $this->postWebhook($activePayload);
        $this->postWebhook($canceledPayload);

        $this->assertNotNull(EntitlementGrant::query()->firstOrFail()->revoked_at);
    }

    public function test_an_invalid_signature_is_rejected_without_recording_an_event(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        $this->app->forgetInstance(StripeWebhookVerifier::class);

        $this->call('POST', '/api/stripe/webhooks', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',
            'CONTENT_TYPE' => 'application/json',
        ], '{}')->assertBadRequest();

        $this->assertSame(0, StripeEvent::query()->count());
    }

    private function postWebhook(string $payload): void
    {
        $this->call('POST', '/api/stripe/webhooks', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $this->signature($payload, 'whsec_test'),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();
    }

    private function subscriptionPayload(string $eventId, string $status): string
    {
        return json_encode([
            'id' => $eventId,
            'object' => 'event',
            'type' => 'customer.subscription.updated',
            'created' => time(),
            'livemode' => false,
            'data' => [
                'object' => [
                    'id' => 'sub_test',
                    'object' => 'subscription',
                    'customer' => 'cus_test',
                    'status' => $status,
                    'items' => [
                        'object' => 'list',
                        'data' => [[
                            'id' => 'si_test',
                            'object' => 'subscription_item',
                            'price' => [
                                'id' => 'price_logres',
                                'object' => 'price',
                            ],
                        ]],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function signature(string $payload, string $secret): string
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        return "t={$timestamp},v1={$signature}";
    }
}
