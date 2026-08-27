<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_public_homepage_describes_the_product(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Logres')
            ->assertSee('Software-development orchestration');
    }

    public function test_the_api_reports_the_service_readiness_state(): void
    {
        $this->get('/api/status')
            ->assertOk()
            ->assertJson([
                'service' => 'accounts',
                'status' => 'ready_for_identity_provider',
                'integrations' => [
                    'stripe' => ['configured' => false],
                ],
            ]);
    }

    public function test_pricing_and_billing_cover_the_product_catalog(): void
    {
        $this->get('/pricing')
            ->assertOk()
            ->assertSee('Logres')
            ->assertSee('Free')
            ->assertSee('Paid')
            ->assertSee('Price pending publication');

        $this->get('/billing')
            ->assertOk()
            ->assertSee('Logres')
            ->assertSee('Stripe-hosted Checkout');
    }

    public function test_every_documented_product_has_a_public_behavior_reference(): void
    {
        foreach (config('products') as $slug => $product) {
            if (! isset($product['documentation'])) {
                continue;
            }

            $this->get(route('products.docs', $slug))
                ->assertOk()
                ->assertSee($product['name'].' behavior')
                ->assertSee($product['availability'])
                ->assertSee($product['documentation']['status']);
        }
    }

    public function test_every_package_product_publishes_free_and_paid_plan_features(): void
    {
        $this->assertCount(8, config('products'));

        foreach (config('products') as $slug => $product) {
            $this->get(route('products.show', $slug))
                ->assertOk()
                ->assertSee('In development')
                ->assertSee('Free')
                ->assertSee('Paid');

            $this->assertNotEmpty($product['plans']['free']['features']);
            $this->assertNotEmpty($product['plans']['paid']['features']);
            $this->assertFalse($product['plans']['free']['stripe_required']);
            $this->assertTrue($product['plans']['paid']['stripe_required']);
        }
    }

    public function test_logres_documentation_distinguishes_planned_behavior_and_boundaries(): void
    {
        $this->get('/products/logres/docs')
            ->assertOk()
            ->assertSee('Free and paid tiers in development')
            ->assertSee('ExecutionRequest')
            ->assertSee('Translated Tasks')
            ->assertSee('TaskPrompt')
            ->assertSee('ExecutionTarget selection')
            ->assertSee('Orb API dispatch')
            ->assertSee('Caller response')
            ->assertSee('Aggregated response')
            ->assertSee('Codex')
            ->assertSee('Claude')
            ->assertSee('logres.access')
            ->assertSee('Hosted execution is not available yet');
    }

    public function test_unknown_product_documentation_returns_not_found(): void
    {
        $this->get('/products/unknown/docs')->assertNotFound();
    }
}
