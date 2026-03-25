<?php

namespace App\Providers;

use App\Application\Content\Observers\PageObserver;
use App\Application\Content\Observers\PostObserver;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Request::is('admin/*'))
            Paginator::defaultView('vendor.pagination.admin');

        Post::observe(PostObserver::class);
        Page::observe(PageObserver::class);
    }
}
