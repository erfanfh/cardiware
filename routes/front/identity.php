<?php

use App\Http\Controllers\Front\Identity\AccountController;
use App\Http\Controllers\Front\Identity\AuthController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware(['guest', ForceTrailingSlash::class])->group(function () {
    Route::get('/', [AuthController::class, 'auth'])->name('auth.index');
});
Route::redirect('login', '/auth')->name('login');

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::prefix('account')->middleware('auth')->name('account.')->controller(AccountController::class)->group(function () {
    Route::get('dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('downloads', [AccountController::class, 'downloads'])->name('downloads');
    Route::get('orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('information', [AccountController::class, 'information'])->name('information');
    Route::get('tickets', [AccountController::class, 'tickets'])->name('tickets');
    Route::get('favorites', [AccountController::class, 'favorites'])->name('favorites');
    Route::get('security', [AccountController::class, 'security'])->name('security');
});

