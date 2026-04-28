<?php

use App\Http\Controllers\Front\Project\ProjectController;
use App\Http\Middleware\ForceTrailingSlash;

Route::controller(ProjectController::class)->middleware(ForceTrailingSlash::class)->name('projects.')->prefix('projects')->group(function () {
    Route::get('{project}', 'detail')->name('detail');
    Route::get('/', 'list')->name('list');
});
