<?php

use App\Http\Controllers\Admin\Identity\RoleController;
use App\Http\Controllers\Admin\Identity\UserController;
use App\Http\Controllers\Admin\Identity\UserSearchController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class)->names('roles');
Route::get('roles/{role}/assign-new-role', [RoleController::class, 'showAssignNewRoleForm'])->name('roles.assign-new-role.form');
Route::post('roles/{role}/assign-new-role', [RoleController::class, 'assignNewRole'])->name('roles.assign-new-role');

Route::resource('users', UserController::class)->except('show');
Route::get('/users/fetch', [UserController::class, 'usersFetch'])->name('users.fetch');
Route::get('/users/ajax-search', UserSearchController::class)->name('users.ajax-search');
