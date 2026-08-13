<?php

use App\Http\Controllers\Admin\Launcher\LauncherDeviceManagementController;
use App\Http\Controllers\Admin\Launcher\LauncherDeviceUserSearchController;
use App\Http\Controllers\Admin\Launcher\LauncherReleaseController;
use App\Http\Controllers\Admin\Launcher\LauncherSlideController;
use Illuminate\Support\Facades\Route;

Route::prefix('launcher-devices')
    ->name('launcher-devices.')
    ->group(function (): void {
        Route::get('/', [LauncherDeviceManagementController::class, 'index'])->name('index');
        Route::get('/users/search', LauncherDeviceUserSearchController::class)->name('users.search');
        Route::post('/reset', [LauncherDeviceManagementController::class, 'reset'])->name('reset');
    });

Route::prefix('launcher-releases')
    ->name('launcher-releases.')
    ->group(function (): void {
        Route::get('/', [LauncherReleaseController::class, 'index'])->name('index');
        Route::post('/', [LauncherReleaseController::class, 'store'])->name('store');
        Route::delete('/{launcherRelease}', [LauncherReleaseController::class, 'destroy'])->name('destroy');
    });

Route::prefix('launcher-slides')
    ->name('launcher-slides.')
    ->group(function (): void {
        Route::get('/', [LauncherSlideController::class, 'index'])->name('index');
        Route::post('/', [LauncherSlideController::class, 'store'])->name('store');
        Route::delete('/{launcherSlide}', [LauncherSlideController::class, 'destroy'])->name('destroy');
    });
