<?php

use App\Http\Controllers\Admin\Organization\TeamMemberController;
use App\Http\Controllers\Admin\Organization\TeamMemberSearchController;

Route::resource('team-members', TeamMemberController::class)->except(['show']);

Route::prefix('team-members')
    ->name('team-members.')
    ->controller(TeamMemberController::class)
    ->group(function () {
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::get('ajax-search', TeamMemberSearchController::class)->name('ajax-search');
    });
