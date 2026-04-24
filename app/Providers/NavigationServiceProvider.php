<?php

namespace App\Providers;

use App\Application\Navigation\Actions\ResolveFrontendNavigationMenuAction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
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
        View::composer('front.header.index', function ($view) {
            $view->with(
                'headerNavigationItems',
                app(ResolveFrontendNavigationMenuAction::class)->execute('header')
            );
        });
    }
}
