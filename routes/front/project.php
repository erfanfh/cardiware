<?php

use App\Http\Controllers\Front\Project\ProjectController;
use App\Http\Middleware\ForceTrailingSlash;

Route::controller(ProjectController::class)->middleware(ForceTrailingSlash::class)->name('projects.')->prefix('projects')->group(function () {
    Route::get('page/{page}', 'list')
        ->whereNumber('page')
        ->name('list.page');

    Route::get('/', 'list')->name('list');

    Route::get('{projectSlug}', 'detail')->name('detail');
});
