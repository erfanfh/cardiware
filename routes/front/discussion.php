<?php

use App\Http\Controllers\Front\Discussion\ListPostCommentsController;
use App\Http\Controllers\Front\Discussion\StorePostCommentController;

Route::get('/posts/{post}/comments', ListPostCommentsController::class)->name('posts.comments.index');
Route::post('/posts/{post}/comments', StorePostCommentController::class)->name('posts.comments.store');
