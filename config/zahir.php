<?php

return [
    'maximum_request_bytes' => (int) env('ZAHIR_MAXIMUM_REQUEST_BYTES', 32768),
    'requests_per_minute' => (int) env('ZAHIR_REQUESTS_PER_MINUTE', 120),
    'identity_link_max_age_seconds' => (int) env('ZAHIR_IDENTITY_LINK_MAX_AGE_SECONDS', 600),
    /*
     * The product catalogue. Each key is a stable product identifier that a
     * consuming application names in its own configuration, and each product's
     * access is decided only by its own entitlement — holding one product's
     * grant never implies another's.
     *
     * `development_account_id` is a deterministic account used only by the
     * development/test seed, so fixtures are reproducible across machines.
     */
    'products' => [
        'logres' => [
            'name' => 'Logres',
            'access_entitlement' => 'access',
            'production_grant_policy' => 'deny_until_launch_policy_approved',
            'grant_owner_role' => 'launch_access_administrator',
            'revocation_source' => 'manual_invitation_registry',
            'development_account_id' => 'acc_01j6g000000000000000000000',
            'seed_reference' => 'zahir-011-v1',
        ],
        'mary-win' => [
            'name' => 'mary.win',
            'access_entitlement' => 'access',
            'production_grant_policy' => 'deny_until_launch_policy_approved',
            'grant_owner_role' => 'launch_access_administrator',
            'revocation_source' => 'manual_invitation_registry',
            'development_account_id' => 'acc_01j6g000000000000000000002',
            'seed_reference' => 'mary-win-v1',
        ],
        'cleverness' => [
            'name' => 'Cleverness',
            'access_entitlement' => 'access',
            'production_grant_policy' => 'deny_until_launch_policy_approved',
            'grant_owner_role' => 'launch_access_administrator',
            'revocation_source' => 'manual_invitation_registry',
            'development_account_id' => 'acc_01j6g000000000000000000003',
            'seed_reference' => 'cleverness-v1',
        ],
        'burdgen' => [
            'name' => 'Burdgen',
            'access_entitlement' => 'access',
            'production_grant_policy' => 'deny_until_launch_policy_approved',
            'grant_owner_role' => 'launch_access_administrator',
            'revocation_source' => 'manual_invitation_registry',
            'development_account_id' => 'acc_01j6g000000000000000000001',
            'seed_reference' => 'mme-2102-v1',
        ],
    ],
    'seed_development_grants' => (bool) env('ZAHIR_SEED_DEVELOPMENT_GRANTS', false),
];
