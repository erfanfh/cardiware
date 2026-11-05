<?php

use App\Http\Controllers\Admin\Loyalty\AdjustAudienceMemberPointsController;
use App\Http\Controllers\Admin\Loyalty\AudienceMemberController;
use App\Http\Controllers\Admin\Loyalty\EarningRuleBulkActionController;
use App\Http\Controllers\Admin\Loyalty\LoyaltyAchievementController;
use App\Http\Controllers\Admin\Loyalty\LoyaltyLevelController;
use App\Http\Controllers\Admin\Loyalty\LoyaltyMissionController;
use App\Http\Controllers\Admin\Loyalty\EarningRuleController;
use App\Http\Controllers\Admin\Loyalty\RewardOfferBulkActionController;
use App\Http\Controllers\Admin\Loyalty\RewardOfferController;
use App\Http\Controllers\Admin\Loyalty\LoyaltyChampionController;
use Illuminate\Support\Facades\Route;

Route::resource('loyalty-levels', LoyaltyLevelController::class)->except(['show']);
Route::resource('loyalty-missions', LoyaltyMissionController::class)->except(['show']);
Route::resource('loyalty-achievements', LoyaltyAchievementController::class)->except(['show']);
Route::resource('earning-rules', EarningRuleController::class)->except(['show']);
Route::post('earning-rules/bulk-action', EarningRuleBulkActionController::class)->name('earning-rules.bulk-action');

Route::resource('reward-offers', RewardOfferController::class)->except(['show']);
Route::post('reward-offers/bulk-action', RewardOfferBulkActionController::class)->name('reward-offers.bulk-action');

Route::get('loyalty-champions', [LoyaltyChampionController::class, 'index'])->name('loyalty-champions.index');
Route::post('loyalty-champions/close', [LoyaltyChampionController::class, 'closeFinished'])->name('loyalty-champions.close');
Route::post('loyalty-champions/{champion}/deliver', [LoyaltyChampionController::class, 'deliverGrandPrize'])->name('loyalty-champions.deliver');
Route::get('loyalty-members', [AudienceMemberController::class, 'index'])->name('loyalty-members.index');
Route::get('loyalty-members/{user}', [AudienceMemberController::class, 'show'])->name('loyalty-members.show');
Route::post('loyalty-members/{user}/adjust', AdjustAudienceMemberPointsController::class)->name('loyalty-members.adjust');
