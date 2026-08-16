<?php

use App\Http\Controllers\Admin\Loyalty\EarningRuleBulkActionController;
use App\Http\Controllers\Admin\Loyalty\EarningRuleController;
use Illuminate\Support\Facades\Route;

Route::resource('earning-rules', EarningRuleController::class)->except(['show']);

Route::post('earning-rules/bulk-action', EarningRuleBulkActionController::class)
    ->name('earning-rules.bulk-action');
