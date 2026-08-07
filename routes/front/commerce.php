<?php

use App\Http\Controllers\Front\Commerce\CartController;
use App\Http\Controllers\Front\Commerce\DownloadPurchasedFileController;
use App\Http\Controllers\Front\Commerce\DownloadLauncherContentFileController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->group(function () {
    Route::get('/cart', CartController::class)->name('cart.index');
});

Route::get('/accesses/{purchaseAccess}/files/{file}', DownloadPurchasedFileController::class)
    ->middleware(['signed', 'throttle:120,1'])
    ->name('downloads.purchase-access.file');

Route::get(
    '/accesses/{purchaseAccess}/{contentType}/{content}/files/{file}',
    DownloadLauncherContentFileController::class
)
    ->middleware(['signed', 'throttle:120,1'])
    ->whereIn('contentType', ['update', 'mod'])
    ->name('downloads.launcher-content.file');
