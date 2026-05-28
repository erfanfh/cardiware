<?php

use App\Http\Controllers\Admin\Marketplace\StoreSearchController;

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('ajax-search', StoreSearchController::class)->name('ajax-search');
});
