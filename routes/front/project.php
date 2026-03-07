<?php

use App\Http\Controllers\Front\Project\ProjectController;

Route::controller(ProjectController::class)->name('projects.')->prefix('projects')->group(function () {
    Route::get('{project}', 'detail')->name('detail');
    Route::get('/', 'list')->name('list');
});
