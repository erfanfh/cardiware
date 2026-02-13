<?php

use App\Http\Controllers\Front\Identity\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'auth'])->name('auth.index');
    Route::get('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
});
Route::redirect('login', 'auth')->name('login');

Route::middleware('auth')->get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
