<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:web'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        
        require __DIR__ . '/admin/identity.php';
        require __DIR__ . '/admin/content.php';
        require __DIR__ . '/admin/taxonomy.php';
    });
