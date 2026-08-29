<?php

namespace Tests;

use App\Models\ServiceCaller;
use App\Services\ServiceCredentials;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function serviceToken(string $key = 'test-client'): string
    {
        $caller = ServiceCaller::query()->create(['key' => $key, 'name' => $key]);

        return app(ServiceCredentials::class)->issue($caller, 'test');
    }
}
