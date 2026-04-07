<?php

use App\Http\Controllers\Admin\Identity\PermissionSearchController;
use App\Http\Controllers\Admin\Identity\RoleBulkActionController;
use App\Http\Controllers\Admin\Identity\RoleController;
use App\Http\Controllers\Admin\Identity\UserBulkActionController;
use App\Http\Controllers\Admin\Identity\UserController;
use App\Http\Controllers\Admin\Identity\UserSearchController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class);
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('{role}/assign-new-role', [RoleController::class, 'showAssignNewRoleForm'])->name('assign-new-role.form');
    Route::post('{role}/assign-new-role', [RoleController::class, 'assignNewRole'])->name('assign-new-role');
    Route::post('bulk-action', RoleBulkActionController::class)->name('bulk-action');
});

Route::resource('users', UserController::class)->except('show');
Route::prefix('users')->name('users.')->group(function () {
    Route::post('bulk-action', UserBulkActionController::class)->name('bulk-action');

    Route::get('fetch', [UserController::class, 'usersFetch'])->name('fetch');

    Route::get('ajax-search', UserSearchController::class)->name('ajax-search');
});
Route::get('permissions/ajax-search', PermissionSearchController::class)->name('permissions.ajax-search');
