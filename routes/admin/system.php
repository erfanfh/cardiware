<?php

use App\Http\Controllers\Admin\System\SettingController;

Route::prefix('settings')->name('settings.')->controller(SettingController::class)->group(function () {
    Route::get('general', 'general')->name('general');
    Route::put('general', 'storeGeneral')->name('general.store');
    Route::get('header', 'header')->name('header');
    Route::put('header', 'storeHeader')->name('header.store');
    Route::get('footer', 'footer')->name('footer');
    Route::put('footer', 'storeFooter')->name('footer.store');
    Route::get('home', 'home')->name('home');
    Route::put('home', 'storeHome')->name('home.store');
});
