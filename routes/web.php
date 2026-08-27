<?php

use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\TrustCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/pricing', [PublicSiteController::class, 'pricing'])->name('pricing');
Route::get('/billing', [PublicSiteController::class, 'billing'])->name('billing');
Route::get('/products/{product}/docs', [PublicSiteController::class, 'documentation'])->name('products.docs');
Route::get('/products/{product}', [PublicSiteController::class, 'product'])->name('products.show');

Route::get('/trust', [TrustCenterController::class, 'index'])->name('trust.index');
Route::get('/legal/{document}', [TrustCenterController::class, 'show'])->name('trust.show');
Route::get('/drafts/legal/{document}', [TrustCenterController::class, 'draft'])->name('trust.draft');
