<?php

use App\Http\Controllers\Admin\Launcher\LauncherDeviceManagementController;
use App\Http\Controllers\Admin\Launcher\LauncherDeviceUserSearchController;
use App\Http\Controllers\Admin\Launcher\LauncherReleaseController;
use App\Http\Controllers\Admin\Launcher\LauncherSlideController;
use App\Http\Controllers\Admin\Launcher\LauncherNotificationController;
use App\Http\Controllers\Admin\Launcher\LauncherGuideController;
use Illuminate\Support\Facades\Route;

Route::prefix('launcher-devices')
    ->name('launcher-devices.')
    ->group(function (): void {
        Route::get('/', [LauncherDeviceManagementController::class, 'index'])->name('index');
        Route::get('/users/search', LauncherDeviceUserSearchController::class)->name('users.search');
        Route::post('/reset', [LauncherDeviceManagementController::class, 'reset'])->name('reset');
        Route::post('/suspend', [LauncherDeviceManagementController::class, 'suspend'])->name('suspend');
        Route::post('/restore', [LauncherDeviceManagementController::class, 'restore'])->name('restore');
    });

Route::get('launcher-security', [LauncherSecurityMonitorController::class, 'index'])
    ->name('launcher-security.index');
Route::get('launcher-security/watermark-lookup', [LauncherSecurityMonitorController::class, 'watermarkLookup'])
    ->name('launcher-security.watermark-lookup');
Route::post('launcher-security/allowed-dlls', [LauncherSecurityMonitorController::class, 'allowDll'])
    ->name('launcher-security.allowed-dlls.store');
Route::delete('launcher-security/allowed-dlls/{allowedDll}', [LauncherSecurityMonitorController::class, 'revokeDll'])
    ->whereNumber('allowedDll')
    ->name('launcher-security.allowed-dlls.destroy');

Route::prefix('launcher-releases')
    ->name('launcher-releases.')
    ->group(function (): void {
        Route::get('/', [LauncherReleaseController::class, 'index'])->name('index');
        Route::put('/public-download', [LauncherReleaseController::class, 'updatePublicDownload'])
            ->name('public-download');
        Route::put('/maintenance', [LauncherReleaseController::class, 'updateMaintenance'])
            ->name('maintenance');
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

Route::prefix('launcher-notifications')
    ->name('launcher-notifications.')
    ->group(function (): void {
        Route::get('/', [LauncherNotificationController::class, 'index'])->name('index');
        Route::post('/', [LauncherNotificationController::class, 'store'])->name('store');
        Route::delete('/{launcherNotification}', [LauncherNotificationController::class, 'destroy'])->name('destroy');
    });

Route::prefix('launcher-guides')
    ->name('launcher-guides.')
    ->group(function (): void {
        Route::get('/', [LauncherGuideController::class, 'index'])->name('index');
        Route::post('/', [LauncherGuideController::class, 'store'])->name('store');
        Route::delete('/{launcherGuide}', [LauncherGuideController::class, 'destroy'])->name('destroy');
    });
