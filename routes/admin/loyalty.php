<?php

use App\Http\Controllers\Admin\Loyalty\EarningRuleBulkActionController;
use App\Http\Controllers\Admin\Loyalty\EarningRuleController;
use App\Http\Controllers\Admin\Loyalty\AudienceMemberController;
use App\Http\Controllers\Admin\Loyalty\AdjustAudienceMemberPointsController;
use Illuminate\Support\Facades\Route;

Route::resource('earning-rules', EarningRuleController::class)->except(['show']);

Route::post('earning-rules/bulk-action', EarningRuleBulkActionController::class)
    ->name('earning-rules.bulk-action');

Route::get('loyalty-members', [AudienceMemberController::class, 'index'])
    ->name('loyalty-members.index');

Route::get('loyalty-members/{user}', [AudienceMemberController::class, 'show'])
    ->name('loyalty-members.show');

Route::post('loyalty-members/{user}/adjust', AdjustAudienceMemberPointsController::class)
    ->name('loyalty-members.adjust');
