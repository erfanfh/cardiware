<?php

use App\Http\Middleware\ForceTrailingSlash;

Route::middleware(ForceTrailingSlash::class)->prefix('stores')->name('stores.')->group(function () {
    Route::get('/', function () {
    })->name('list');
    Route::get('/{storeSlug}', function () {
    })->name('detail');
});
