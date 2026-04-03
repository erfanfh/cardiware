<?php

use App\Http\Controllers\Admin\Project\ProjectBulkActionController;
use App\Http\Controllers\Admin\Project\ProjectController;
use App\Http\Controllers\Admin\Project\ProjectSearchController;
use App\Http\Controllers\Admin\Project\QuickUpdateProjectController;

Route::resource('projects', ProjectController::class)->except(['show']);

Route::prefix('projects')->name('projects.')->group(function () {
    Route::post('bulk-action', ProjectBulkActionController::class)->name('bulk-action');

    Route::patch('{project}/quick-update', QuickUpdateProjectController::class)->name('quick-update');

    Route::get('ajax-search', ProjectSearchController::class)->name('ajax-search');
});

