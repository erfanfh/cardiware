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

        Route::post('otp/request', [AuthController::class, 'requestLoginOtp'])
            ->middleware('throttle:api-login')
            ->name('otp.request');

        Route::post('otp/verify', [AuthController::class, 'loginWithOtp'])
            ->middleware('throttle:api-login')
            ->name('otp.verify');

        Route::post('logout', [AuthController::class, 'logout'])
            ->middleware([
                'auth:sanctum',
                'user.active',
                'launcher.account',
                'launcher.device',
            ])
            ->name('logout');
    });

Route::get('me', MeController::class)
    ->middleware([
        'auth:sanctum',
        'user.active',
        'launcher.account',
        'launcher.device',
    ])
    ->name('me');
