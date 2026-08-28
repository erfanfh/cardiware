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

Route::get('/project-sitemap.xml', [SitemapController::class, 'projects'])
    ->name('sitemap.projects');

Route::get('/project-sitemap{page}.xml', [SitemapController::class, 'paginatedProjects'])
    ->whereNumber('page')
    ->name('sitemap.projects.paginated');

Route::get('/page-sitemap.xml', [SitemapController::class, 'pages'])
    ->name('sitemap.pages');

Route::get('/page-sitemap{page}.xml', [SitemapController::class, 'paginatedPages'])
    ->whereNumber('page')
    ->name('sitemap.pages.paginated');

Route::get('/category-sitemap.xml', [SitemapController::class, 'categories'])
    ->name('sitemap.categories');

Route::get('/category-sitemap{page}.xml', [SitemapController::class, 'paginatedCategories'])
    ->whereNumber('page')
    ->name('sitemap.categories.paginated');

Route::get('/post_tag-sitemap.xml', [SitemapController::class, 'tags'])
    ->name('sitemap.tags');

Route::get('/post_tag-sitemap{page}.xml', [SitemapController::class, 'paginatedTags'])
    ->whereNumber('page')
    ->name('sitemap.tags.paginated');

Route::get('/author-sitemap.xml', [SitemapController::class, 'authors'])
    ->name('sitemap.authors');

Route::get('/author-sitemap{page}.xml', [SitemapController::class, 'paginatedAuthors'])
    ->whereNumber('page')
    ->name('sitemap.authors.paginated');

Route::get('/product-sitemap.xml', [SitemapController::class, 'products'])
    ->name('sitemap.products');

Route::get('/product-sitemap{page}.xml', [SitemapController::class, 'paginatedProducts'])
    ->whereNumber('page')
    ->name('sitemap.products.paginated');

Route::get('/video-sitemap.xml', [SitemapController::class, 'videos'])->name('sitemap.videos');
Route::get('/video-sitemap{page}.xml', [SitemapController::class, 'paginatedVideos'])->whereNumber('page')->name('sitemap.videos.paginated');
