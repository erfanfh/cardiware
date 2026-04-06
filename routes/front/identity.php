<?php

use App\Http\Controllers\Front\Identity\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'auth'])->name('auth.index');
});
Route::redirect('login', 'auth')->name('login');

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('account', function () {
})->name('account');
