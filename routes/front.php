<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->group(function () {
        Route::get('/', function () {
        })->name('home');

        require __DIR__ . '/front/identity.php';
    });
