<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:web', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        require __DIR__ . '/admin/identity.php';
        require __DIR__ . '/admin/content.php';
        require __DIR__ . '/admin/taxonomy.php';
        require __DIR__ . '/admin/project.php';
        require __DIR__ . '/admin/system.php';
        require __DIR__ . '/admin/discussion.php';
        require __DIR__ . '/admin/layout.php';
        require __DIR__ . '/admin/slug.php';
        require __DIR__ . '/admin/navigation.php';
    });
