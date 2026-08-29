<?php

namespace Tests;

use App\Models\ServiceCaller;
use App\Services\ServiceCredentials;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function serviceToken(string $key = 'test-client', bool $canManageLifecycle = false): string
    {
        $caller = ServiceCaller::query()->create([
            'key' => $key,
            'name' => $key,
            'can_manage_account_lifecycle' => $canManageLifecycle,
        ]);

        return app(ServiceCredentials::class)->issue($caller, 'test');
    }
}
