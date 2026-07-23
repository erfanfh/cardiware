<?php

use App\Http\Controllers\Admin\Organization\TeamMemberController;

Route::resource('team-members', TeamMemberController::class)->except(['show']);

Route::prefix('team-members')
    ->name('team-members.')
    ->controller(TeamMemberController::class)
    ->group(function () {
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
    });
