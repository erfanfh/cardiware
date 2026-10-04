<?php

use App\Http\Controllers\Front\Marketplace\SellerDashboardController;
use App\Http\Controllers\Front\Marketplace\SellerStoreController;
use App\Http\Middleware\EnsureSellerStoreAccess;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('seller')->name('seller.')->group(function (): void {
    Route::get('dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    Route::prefix('stores/{store}')->name('stores.')->middleware(EnsureSellerStoreAccess::class)->group(function (): void {
        Route::get('dashboard', [SellerDashboardController::class, 'show'])->name('dashboard');
        Route::get('products', [SellerStoreController::class, 'products'])->name('products');
        Route::get('comments', [SellerStoreController::class, 'comments'])->name('comments');
        Route::get('tickets', [SellerStoreController::class, 'tickets'])->name('tickets');
        Route::get('tickets/{ticket}', [SellerStoreController::class, 'showTicket'])->name('tickets.show');
        Route::post('tickets/{ticket}/reply', [SellerStoreController::class, 'replyTicket'])->middleware('throttle:10,1')->name('tickets.reply');
    });
});
