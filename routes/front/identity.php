<?php

use App\Http\Controllers\Front\Identity\AccountController;
use App\Http\Controllers\Front\Identity\AuthController;
use App\Http\Controllers\Front\Identity\UserNotificationController;
use App\Http\Controllers\Front\Affiliate\AffiliatePanelController;
use App\Http\Controllers\Front\Affiliate\StoreAffiliateWithdrawalController;
use App\Http\Controllers\Front\Loyalty\ConvertPointsToWalletController;
use App\Http\Controllers\Front\Loyalty\RedeemRewardOfferController;
use App\Http\Controllers\Front\Financial\WalletWithdrawalController;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware(['guest', ForceTrailingSlash::class])->group(function () {
    Route::get('/', [AuthController::class, 'auth'])->name('auth.index');
});
Route::redirect('login', '/auth')->name('login');

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::prefix('account')->middleware('auth')->name('account.')->group(function () {
    Route::get('notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [UserNotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [UserNotificationController::class, 'read'])->name('notifications.read');
    Route::get('wallet/withdrawals', [WalletWithdrawalController::class, 'index'])->name('wallet.withdrawals.index');
    Route::post('wallet/withdrawals', [WalletWithdrawalController::class, 'store'])
        ->middleware('throttle:5,1')->name('wallet.withdrawals.store');
    Route::controller(AccountController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('downloads', 'downloads')->name('downloads');
        Route::get('orders', 'orders')->name('orders');
        Route::get('information', 'information')->name('information');
        Route::get('tickets', 'tickets')->name('tickets');
        Route::get('favorites', 'favorites')->name('favorites');
        Route::get('transactions', 'transactions')->name('transactions');
        Route::get('security', 'security')->name('security');
        Route::get('wallet', 'wallet')->name('wallet');
        Route::get('loyalty', 'loyalty')->name('loyalty');
    });

    Route::put('loyalty/vip-profile', \App\Http\Controllers\Front\Loyalty\UpdateVipClubProfileController::class)
        ->middleware('throttle:10,1')
        ->name('loyalty.vip-profile.update');

    Route::post('loyalty/rewards/{rewardOffer}/redeem', RedeemRewardOfferController::class)
        ->middleware('throttle:20,1')
        ->name('loyalty.rewards.redeem');

    Route::post('loyalty/convert-to-wallet', ConvertPointsToWalletController::class)
        ->middleware('throttle:10,1')
        ->name('loyalty.convert-to-wallet');

    Route::get('affiliate', AffiliatePanelController::class)->name('affiliate');
    Route::post('affiliate/withdrawals', StoreAffiliateWithdrawalController::class)
        ->middleware('throttle:5,1')
        ->name('affiliate.withdrawals.store');
});
