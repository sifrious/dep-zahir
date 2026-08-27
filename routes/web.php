<?php

use App\Http\Controllers\TrustCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
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

Route::get('/trust', [TrustCenterController::class, 'index'])->name('trust.index');
Route::get('/legal/{document}', [TrustCenterController::class, 'show'])->name('trust.show');
