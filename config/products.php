<?php

return [
    'logres' => [
        'name' => 'Logres',
        'summary' => 'Hosted software-development orchestration for turning requests into executable tasks and verified results.',
        'description' => 'Logres accepts a software-development request, translates it into explicit tasks and prompts, dispatches those tasks to isolated execution targets, streams execution feedback, and returns the resulting code, tests, and artifacts.',
        'delivery' => 'Logres is a digitally delivered hosted service. Account access is provided online after successful signup and any required payment.',
        'plans' => [
            'standard' => [
                'name' => 'Standard',
                'price' => env('BUSINESS_LOGRES_PRICE'),
                'currency' => env('BUSINESS_LOGRES_CURRENCY', 'USD'),
                'billing_period' => env('BUSINESS_LOGRES_BILLING_PERIOD', 'month'),
                'stripe_price_id' => env('STRIPE_LOGRES_PRICE_ID'),
                'entitlement' => 'logres.access',
                'features' => [
                    'Submit software-development execution requests',
                    'Receive translated tasks and execution feedback',
                    'Review resulting code, tests, and artifacts',
                ],
            ],
        ],
    ],
];
