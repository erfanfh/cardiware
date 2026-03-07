<?php

use App\Http\Controllers\Front\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        require __DIR__ . '/front/identity.php';
    });
