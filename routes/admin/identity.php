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

    Route::resource('roles', RoleController::class)
        ->names('roles')->except(['show', 'destroy']);

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
    });
});
