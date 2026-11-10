<?php

use App\Http\Controllers\Admin\Identity\PermissionSearchController;
use App\Http\Controllers\Admin\Identity\RoleBulkActionController;
use App\Http\Controllers\Admin\Identity\RoleController;
use App\Http\Controllers\Admin\Identity\RoleLoginAlertsController;
use App\Http\Controllers\Admin\Identity\UserBulkActionController;
use App\Http\Controllers\Admin\Identity\UserController;
use App\Http\Controllers\Admin\Identity\UserSearchController;
use App\Http\Controllers\Admin\Identity\UserWalletController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class)->except('show');
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('{role}/login-alerts', [RoleLoginAlertsController::class, 'edit'])->name('login-alerts.edit');
    Route::put('{role}/login-alerts', [RoleLoginAlertsController::class, 'update'])->name('login-alerts.update');
    Route::post('bulk-action', RoleBulkActionController::class)->name('bulk-action');
});

Route::resource('users', UserController::class)->except('show');
Route::prefix('users')->name('users.')->group(function () {
    Route::get('{user}/wallet', [UserWalletController::class, 'show'])->name('wallet.show');
    Route::post('{user}/wallet', [UserWalletController::class, 'adjust'])->name('wallet.adjust');
    Route::post('bulk-action', UserBulkActionController::class)->name('bulk-action');

    Route::post('{user}/club-bio', [UserController::class, 'moderateClubBio'])->name('club-bio.moderate');

    Route::get('fetch', [UserController::class, 'usersFetch'])->name('fetch');

    Route::get('ajax-search', UserSearchController::class)->name('ajax-search');
});
Route::get('permissions/ajax-search', PermissionSearchController::class)->name('permissions.ajax-search');
