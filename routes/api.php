<?php

use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json([
        'service' => 'accounts',
        'status' => 'ready_for_identity_provider',
        'health' => url('/up'),
        'integrations' => [
            'stripe' => [
                'configured' => filled(config('services.stripe.secret'))
                    && filled(config('services.stripe.webhook_secret'))
                    && filled(config('services.stripe.prices.logres.price_id')),
            ],
        ],
    ]);
});

Route::post('/stripe/webhooks', StripeWebhookController::class)->name('stripe.webhooks');
