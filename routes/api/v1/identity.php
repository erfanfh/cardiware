<?php

use App\Http\Controllers\Api\V1\Identity\AuthController;
use App\Http\Controllers\Api\V1\Identity\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:api-login')
            ->name('login');

        Route::post('logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('logout');
    });

Route::get('me', MeController::class)
    ->middleware('auth:sanctum')
    ->name('me');
