<?php

use App\Http\Controllers\Front\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap_index.xml', [SitemapController::class, 'index'])
    ->name('sitemap.index');

Route::get('/post-sitemap.xml', [SitemapController::class, 'posts'])
    ->name('sitemap.posts');

Route::get('/post-sitemap{page}.xml', [SitemapController::class, 'paginatedPosts'])
    ->whereNumber('page')
    ->name('sitemap.posts.paginated');

Route::get('/page-sitemap.xml', [SitemapController::class, 'pages'])
    ->name('sitemap.pages');

Route::get('/category-sitemap.xml', [SitemapController::class, 'categories'])
    ->name('sitemap.categories');

Route::get('/post_tag-sitemap.xml', [SitemapController::class, 'tags'])
    ->name('sitemap.tags');

Route::get('/post_tag-sitemap{page}.xml', [SitemapController::class, 'paginatedTags'])
    ->whereNumber('page')
    ->name('sitemap.tags.paginated');

Route::get('/author-sitemap.xml', [SitemapController::class, 'authors'])
    ->name('sitemap.authors');
