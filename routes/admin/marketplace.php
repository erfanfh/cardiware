<?php

use App\Http\Controllers\Admin\Marketplace\StoreController;
use App\Http\Controllers\Admin\Marketplace\StoreSearchController;

Route::resource('stores', StoreController::class)->except(['show']);

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('ajax-search', StoreSearchController::class)->name('ajax-search');
});
