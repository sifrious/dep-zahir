<?php

namespace Database\Seeders;

use App\Models\AccountLifecycleEvent;
use App\Models\AccountResolutionEvent;
use App\Models\ExternalIdentity;
use App\Models\ServiceCaller;
use App\Models\ServiceRequestEvent;
use Illuminate\Database\Seeder;

final class ReleaseRehearsalSeeder extends Seeder
{
    public function run(): void
    {
        config(['zahir.seed_development_grants' => true]);
        $this->call(LogresProductSeeder::class);
        $accountId = LogresProductSeeder::DEVELOPMENT_ACCOUNT_ID;

        ExternalIdentity::query()->firstOrCreate([
            'provider' => 'rehearsal-idp',
            'provider_subject' => 'release-rehearsal-subject',
        ], [
            'account_id' => $accountId,
            'verified_claims' => [],
            'provenance' => ['issuer' => 'rehearsal', 'audience' => 'rehearsal', 'asserted_at' => now()->toIso8601String()],
            'linked_at' => now(),
            'last_authenticated_at' => now(),
        ]);
        AccountResolutionEvent::query()->firstOrCreate([
            'account_id' => $accountId,
            'provider' => 'rehearsal-idp',
            'provider_subject_hash' => hash('sha256', 'release-rehearsal-subject'),
            'outcome' => 'rehearsal',
        ], [
            'caller' => 'release-rehearsal',
            'provenance' => [],
            'occurred_at' => now(),
        ]);
        AccountLifecycleEvent::query()->firstOrCreate([
            'account_id' => $accountId,
            'from_status' => 'active',
            'to_status' => 'active',
            'caller' => 'release-rehearsal',
            'reason' => 'restore verification fixture',
        ], ['occurred_at' => now()]);

        $caller = ServiceCaller::query()->firstOrCreate(
            ['key' => 'release-rehearsal'],
            ['name' => 'Release rehearsal'],
        );
        $credential = $caller->credentials()->firstOrCreate(
            ['label' => 'restore-fixture'],
            ['secret_hash' => password_hash('non-production-fixture', PASSWORD_DEFAULT)],
        );
        ServiceRequestEvent::query()->firstOrCreate([
            'request_id' => 'release-rehearsal-request',
        ], [
            'service_caller_id' => $caller->id,
            'service_credential_id' => $credential->id,
            'caller_key' => $caller->key,
            'method' => 'POST',
            'route' => 'api/v1/accounts/resolve',
            'response_status' => 200,
            'occurred_at' => now(),
        ]);
    }
}
