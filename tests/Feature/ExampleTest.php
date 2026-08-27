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
}
