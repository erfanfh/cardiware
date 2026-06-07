<?php

use App\Http\Controllers\Front\Commerce\CartController;
use App\Http\Controllers\Front\Commerce\DownloadPurchasedFileController;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::get('/downloads/accesses/{purchaseAccess}/files/{file}', DownloadPurchasedFileController::class)
    ->middleware(['auth', 'signed'])
    ->name('downloads.purchase-access.file');
