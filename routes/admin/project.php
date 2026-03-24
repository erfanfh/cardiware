<?php

use App\Http\Controllers\Admin\Project\ProjectController;

Route::resource('projects', ProjectController::class)->except(['show']);

Route::get('/projects/check-slug', [ProjectController::class, 'checkSlug'])
    ->name('projects.check-slug');

Route::get('/projects/bulk-action', function () {
})->name('projects.bulk-action');

Route::patch('/projects/{project}/quick-update', function () {
})->name('projects.quick-update');
