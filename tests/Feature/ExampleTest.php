<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_service_reports_its_readiness_state(): void
    {
        $this->get('/')
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
