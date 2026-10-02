<?php

use App\Http\Controllers\Front\Marketplace\SellerDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('seller')->name('seller.')->group(function (): void {
    Route::get('dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    Route::get('stores/{store}/dashboard', [SellerDashboardController::class, 'show'])->name('stores.dashboard');
});
