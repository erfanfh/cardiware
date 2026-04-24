<?php

namespace App\Providers;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Domain\Layout\Models\PageLayout;
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
        View::composer('front.sidebar.index', function ($view) {
            $view->with(
                'sidebarLayoutData',
                app(GetPageLayoutRenderDataAction::class)->execute(PageLayout::where('key', 'sidebar')->first())
            );
        });
    }
}
