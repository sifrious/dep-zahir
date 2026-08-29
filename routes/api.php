<?php

use App\Http\Controllers\AccountResolutionController;
use App\Http\Controllers\EntitlementDecisionController;
use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json(['service' => 'zahir', 'status' => 'ready']));

Route::middleware(['zahir.request-size', 'throttle:zahir', 'zahir.service'])->prefix('v1')->group(function (): void {
    Route::post('/accounts/resolve', AccountResolutionController::class);
    Route::post('/entitlements/decide', EntitlementDecisionController::class);
});
