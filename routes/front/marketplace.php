<?php

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('/', function () {
    })->name('list');
    Route::get('/{storeSlug}', function () {
    })->name('detail');
});
