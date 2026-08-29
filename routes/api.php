<?php

use App\Http\Controllers\AccountLifecycleController;
use App\Http\Controllers\AccountResolutionController;
use App\Http\Controllers\EntitlementDecisionController;
use App\Http\Controllers\IdentityLinkController;
use App\Http\Controllers\IdentityUnlinkController;
use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json(['service' => 'zahir', 'status' => 'ready']));

Route::middleware(['zahir.request-size', 'throttle:zahir', 'zahir.service'])->prefix('v1')->group(function (): void {
    Route::post('/accounts/resolve', AccountResolutionController::class);
    Route::post('/accounts/{account}/identities/link', IdentityLinkController::class);
    Route::delete('/accounts/{account}/identities', IdentityUnlinkController::class);
    Route::post('/accounts/{account}/suspension', [AccountLifecycleController::class, 'suspend']);
    Route::delete('/accounts/{account}/suspension', [AccountLifecycleController::class, 'reactivate']);
    Route::post('/entitlements/decide', EntitlementDecisionController::class);
});
