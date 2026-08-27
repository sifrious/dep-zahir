<?php

use App\Http\Controllers\TrustCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'accounts',
        'status' => 'ready_for_identity_provider',
        'health' => url('/up'),
    ]);
});

Route::get('/trust', [TrustCenterController::class, 'index'])->name('trust.index');
Route::get('/legal/{document}', [TrustCenterController::class, 'show'])->name('trust.show');
