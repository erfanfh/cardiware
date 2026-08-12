<?php

use App\Http\Controllers\Admin\Launcher\LauncherDeviceManagementController;
use App\Http\Controllers\Admin\Launcher\LauncherDeviceUserSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('launcher-devices')
    ->name('launcher-devices.')
    ->group(function (): void {
        Route::get('/', [LauncherDeviceManagementController::class, 'index'])->name('index');
        Route::get('/users/search', LauncherDeviceUserSearchController::class)->name('users.search');
        Route::post('/reset', [LauncherDeviceManagementController::class, 'reset'])->name('reset');
    });
