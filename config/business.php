<?php

return [
    'name' => env('BUSINESS_NAME'),
    'support_email' => env('BUSINESS_SUPPORT_EMAIL'),
    'support_phone' => env('BUSINESS_SUPPORT_PHONE'),
    'address' => env('BUSINESS_ADDRESS'),
    'policy_version' => env('BUSINESS_POLICY_VERSION'),
    'policy_effective_at' => env('BUSINESS_POLICY_EFFECTIVE_AT'),
    'initial_refund_days' => env('BUSINESS_INITIAL_REFUND_DAYS'),
    'renewal_refund_days' => env('BUSINESS_RENEWAL_REFUND_DAYS'),
    'cancellation_effective' => env('BUSINESS_CANCELLATION_EFFECTIVE'),
    'delivery_timing' => env('BUSINESS_DELIVERY_TIMING'),
];
