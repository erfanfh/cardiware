<?php

use App\Http\Controllers\Admin\Icon\IconLookupController;
use Illuminate\Support\Facades\Route;

Route::prefix('icons')
    ->name('icons.')
    ->group(function () {
        Route::get('/lookup', IconLookupController::class)->name('lookup');
    });
