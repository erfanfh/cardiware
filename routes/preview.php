<?php

use App\Http\Controllers\Front\Catalog\ProductDetailController;
use App\Http\Controllers\Front\Content\ContentController;
use App\Http\Controllers\Front\Project\ProjectController;
use App\Http\Controllers\Front\Video\VideoController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ProtectContentPreview;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:web', EnsureUserIsAdmin::class, ProtectContentPreview::class])
    ->prefix('preview')
    ->name('preview.')
    ->group(function (): void {
        Route::get('products/{product}', [ProductDetailController::class, 'preview'])
            ->whereNumber('product')
            ->name('products');

        Route::get('posts/{post}', [ContentController::class, 'previewPost'])
            ->whereNumber('post')
            ->name('posts');

        Route::get('videos/{video}', [VideoController::class, 'preview'])
            ->whereNumber('video')
            ->name('videos');

        Route::get('pages/{page}', [ContentController::class, 'previewPage'])
            ->whereNumber('page')
            ->name('pages');

        Route::get('projects/{project}', [ProjectController::class, 'preview'])
            ->whereNumber('project')
            ->name('projects');
    });
