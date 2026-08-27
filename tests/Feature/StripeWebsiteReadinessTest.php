<?php

namespace Tests\Feature;

use Tests\TestCase;

class StripeWebsiteReadinessTest extends TestCase
{
    public function test_the_readiness_check_identifies_missing_public_information(): void
    {
        $this->artisan('accounts:stripe-readiness')
            ->expectsOutput('The Accounts public site is not ready for Stripe website review.')
            ->expectsOutput('- Privacy Policy')
            ->assertFailed();
    }

    public function test_the_readiness_check_passes_when_facts_and_policies_are_published(): void
    {
        config()->set('business.name', 'Example Business');
        config()->set('business.support_email', 'support@example.test');
        config()->set('business.support_phone', '+15555550100');
        config()->set('business.products.logres.price', '25.00');
        config()->set('services.stripe.secret', 'sk_test_example');
        config()->set('services.stripe.webhook_secret', 'whsec_example');
        config()->set('services.stripe.prices.logres.price_id', 'price_example');

        foreach (['privacy', 'terms', 'refunds', 'cancellations', 'delivery'] as $document) {
            config()->set("trust.documents.{$document}.published", true);
        }

        $this->artisan('accounts:stripe-readiness')
            ->expectsOutput('The Accounts public site is ready for Stripe website review.')
            ->assertSuccessful();
    }
}
