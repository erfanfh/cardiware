<?php

use App\Http\Controllers\Front\Commerce\CartController;
use App\Http\Controllers\Front\Commerce\DownloadPurchasedFileController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->group(function () {
    Route::get('/cart', CartController::class)->name('cart.index');
});

Route::get('/downloads/accesses/{purchaseAccess}/files/{file}', DownloadPurchasedFileController::class)
    ->middleware(['auth', 'signed'])
    ->name('downloads.purchase-access.file');
