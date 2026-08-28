<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        require __DIR__ . '/api/v1/identity.php';
        require __DIR__ . '/api/v1/launcher.php';
        require __DIR__ . '/api/v1/library.php';
        require __DIR__ . '/api/v1/catalog.php';
        require __DIR__ . '/api/v1/download.php';
        require __DIR__ . '/api/v1/poll.php';
    });
