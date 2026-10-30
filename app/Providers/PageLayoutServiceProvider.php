<?php

namespace App\Providers;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Application\Story\Actions\GetVisibleStoriesAction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PageLayoutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('front.home.partials.story', function ($view) {
            $view->with('stories', app(GetVisibleStoriesAction::class)->execute());
        });

        View::composer('front.sidebar.index', function ($view) {
            $view->with(
                'sidebarLayoutData',
                app(GetPageLayoutRenderDataAction::class)->execute('sidebar')
            );
        });
    }
}
