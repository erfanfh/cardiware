<?php

use App\Http\Controllers\Admin\Discussion\CommentBulkActionController;
use App\Http\Controllers\Admin\Discussion\CommentListController;
use App\Http\Controllers\Admin\Discussion\CommentReplyController;
use App\Http\Controllers\Admin\Discussion\CommentTogglePinController;
use Illuminate\Support\Facades\Route;

Route::prefix('comments')
    ->name('comments.')
    ->group(function () {
        Route::get('/', CommentListController::class)->name('index');
        Route::post('/bulk-action', CommentBulkActionController::class)->name('bulk-action');
        Route::post('/{comment}/toggle-pin', CommentTogglePinController::class)->name('toggle-pin');
        Route::post('/{comment}/reply', CommentReplyController::class)->name('reply');
    });
