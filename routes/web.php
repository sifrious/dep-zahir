<?php

use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\TrustCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/products/{product}', [PublicSiteController::class, 'product'])->name('products.show');

Route::get('/trust', [TrustCenterController::class, 'index'])->name('trust.index');
Route::get('/legal/{document}', [TrustCenterController::class, 'show'])->name('trust.show');
