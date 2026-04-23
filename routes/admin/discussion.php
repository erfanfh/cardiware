<?php

use App\Http\Controllers\Admin\Discussion\CommentBulkActionController;
use App\Http\Controllers\Admin\Discussion\CommentListController;
use Illuminate\Support\Facades\Route;

Route::prefix('comments')
    ->name('comments.')
    ->group(function () {
        Route::get('/', CommentListController::class)->name('index');
        Route::post('/bulk-action', CommentBulkActionController::class)->name('bulk-action');
    });
