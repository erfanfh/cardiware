<?php

use App\Http\Controllers\Admin\Affiliate\AffiliateController;
use App\Http\Controllers\Admin\Affiliate\AffiliateWithdrawalController;

Route::resource('affiliates', AffiliateController::class);

Route::get('affiliate-withdrawals', [AffiliateWithdrawalController::class, 'index'])
    ->name('affiliate-withdrawals.index');
Route::post('affiliate-withdrawals/{withdrawal}/resolve', [AffiliateWithdrawalController::class, 'resolve'])
    ->name('affiliate-withdrawals.resolve');
