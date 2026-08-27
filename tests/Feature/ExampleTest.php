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
            ->assertSee('software-development orchestration');
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
            ->assertSee('Standard')
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

    public function test_logres_documentation_distinguishes_planned_behavior_and_boundaries(): void
    {
        $this->get('/products/logres/docs')
            ->assertOk()
            ->assertSee('Planned MVP contract')
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
            ->assertSee('not a currently available execution service');
    }

    public function test_unknown_product_documentation_returns_not_found(): void
    {
        $this->get('/products/unknown/docs')->assertNotFound();
    }
}
