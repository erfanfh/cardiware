<?php

use App\Http\Controllers\Front\Marketplace\StoreDetailController;
use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->prefix('stores')->name('stores.')->group(function () {
    Route::get('/{storeSlug}', StoreDetailController::class)->name('detail');
});
