<?php

use App\Http\Controllers\Admin\Identity\QuickUpdateUserController;
use App\Http\Controllers\Admin\Identity\RoleController;
use App\Http\Controllers\Admin\Identity\UserBulkActionController;
use App\Http\Controllers\Admin\Identity\UserController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class)->names('roles');
Route::get('roles/{role}/assign-new-role', [RoleController::class, 'showAssignNewRoleForm'])->name('roles.assign-new-role.form');
Route::post('roles/{role}/assign-new-role', [RoleController::class, 'assignNewRole'])->name('roles.assign-new-role');

Route::resource('users', UserController::class)->names('users');
Route::post('/users/bulk-action', UserBulkActionController::class)->name('users.bulk-action');
Route::get('/users/fetch', [UserController::class, 'usersFetch'])->name('users.fetch');
