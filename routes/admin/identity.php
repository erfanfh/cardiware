<?php

use App\Http\Controllers\Admin\Identity\RoleController;
use App\Http\Controllers\Admin\Identity\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Identity Roles Routes
|--------------------------------------------------------------------------
|
| Routes for managing roles and users in admin panel
|
*/
Route::prefix('identity')->name('admin.identity.')->group(function () {

    //roles
    Route::resource('roles', RoleController::class)->names('roles');
    Route::get('roles/{role}/assign-new-role', [RoleController::class, 'showAssignNewRoleForm'])->name('roles.assign-new-role.form');
    Route::post('roles/{role}/assign-new-role', [RoleController::class, 'assignNewRole'])->name('roles.assign-new-role');

    //users
    Route::resource('users', UserController::class)->names('users');
});
