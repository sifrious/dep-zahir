<?php

return [
    'maximum_request_bytes' => (int) env('ZAHIR_MAXIMUM_REQUEST_BYTES', 32768),
    'requests_per_minute' => (int) env('ZAHIR_REQUESTS_PER_MINUTE', 120),
    'products' => [
        'logres' => [
            'name' => 'Logres',
            'access_entitlement' => 'access',
            'production_grant_policy' => 'deny_until_launch_policy_approved',
            'grant_owner_role' => 'launch_access_administrator',
            'revocation_source' => 'manual_invitation_registry',
        ],
    ],
    'seed_development_grants' => (bool) env('ZAHIR_SEED_DEVELOPMENT_GRANTS', false),
];
