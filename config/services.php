<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'active_subscription_statuses' => ['active', 'trialing'],
        'prices' => [
            'accounts-client' => [
                'price_id' => env('STRIPE_ACCOUNTS_CLIENT_PAID_PRICE_ID'),
                'product' => 'accounts-client',
                'product_name' => 'Accounts Client',
                'entitlement' => 'accounts-client.access',
            ],
            'aleph' => [
                'price_id' => env('STRIPE_ALEPH_PAID_PRICE_ID'),
                'product' => 'aleph',
                'product_name' => 'Aleph',
                'entitlement' => 'aleph.access',
            ],
            'bindle' => [
                'price_id' => env('STRIPE_BINDLE_PAID_PRICE_ID'),
                'product' => 'bindle',
                'product_name' => 'Bindle',
                'entitlement' => 'bindle.access',
            ],
            'burdgeon' => [
                'price_id' => env('STRIPE_BURDGEON_PAID_PRICE_ID'),
                'product' => 'burdgeon',
                'product_name' => 'Burdgeon',
                'entitlement' => 'burdgeon.access',
            ],
            'funes' => [
                'price_id' => env('STRIPE_FUNES_PAID_PRICE_ID'),
                'product' => 'funes',
                'product_name' => 'Funes',
                'entitlement' => 'funes.access',
            ],
            'kilgore' => [
                'price_id' => env('STRIPE_KILGORE_PAID_PRICE_ID'),
                'product' => 'kilgore',
                'product_name' => 'Kilgore',
                'entitlement' => 'kilgore.access',
            ],
            'logres' => [
                'price_id' => env('STRIPE_LOGRES_PAID_PRICE_ID'),
                'product' => 'logres',
                'product_name' => 'Logres',
                'entitlement' => 'logres.access',
            ],
            'menard' => [
                'price_id' => env('STRIPE_MENARD_PAID_PRICE_ID'),
                'product' => 'menard',
                'product_name' => 'Menard',
                'entitlement' => 'menard.access',
            ],
        ],
    ],

];
