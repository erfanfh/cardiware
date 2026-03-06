<?php

use App\Http\Controllers\Admin\Discussion\CommentController;

Route::resource('comments', CommentController::class);
