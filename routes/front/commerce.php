<?php

use App\Http\Controllers\Front\Commerce\CartController;
use App\Http\Controllers\Front\Commerce\DownloadPurchasedFileController;
use App\Http\Controllers\Front\Commerce\DownloadVipSaleItemFileController;
use App\Http\Controllers\Front\Commerce\DownloadLauncherContentFileController;
use App\Http\Controllers\Front\Commerce\StreamLauncherDownloadController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->group(function () {
    Route::get('/cart', CartController::class)->name('cart.index');
});

Route::get('/accesses/{purchaseAccess}/files/{file}', DownloadPurchasedFileController::class)
    ->middleware(['signed', 'throttle:120,1'])
    ->name('downloads.purchase-access.file');

Route::get('/vip-sale-items/{saleItem}/files/{file}', DownloadVipSaleItemFileController::class)
    ->middleware(['auth', 'signed', 'throttle:120,1'])
    ->whereNumber('saleItem')
    ->whereNumber('file')
    ->name('downloads.vip-sale-item.file');

Route::get(
    '/accesses/{purchaseAccess}/{contentType}/{content}/files/{file}',
    DownloadLauncherContentFileController::class
)
    ->middleware(['signed', 'throttle:120,1'])
    ->whereIn('contentType', ['update', 'mod'])
    ->name('downloads.launcher-content.file');

Route::get(
    '/launcher-downloads/{purchaseAccess}/{contentType}/{content}/files/{file}/{downloadSessionId}',
    StreamLauncherDownloadController::class
)
    ->middleware(['signed', 'throttle:240,1'])
    ->whereNumber('purchaseAccess')
    ->whereIn('contentType', ['product', 'update', 'mod'])
    ->whereNumber('content')
    ->whereNumber('file')
    ->whereUuid('downloadSessionId')
    ->name('downloads.launcher.session-file');
