<?php

use App\Http\Controllers\Front\Discussion\StorePostCommentController;

Route::post('/posts/{post}/comments', StorePostCommentController::class)->name('posts.comments.store');
