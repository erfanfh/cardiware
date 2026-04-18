<?php

namespace App\Providers;

use App\Application\Content\Actions\ResolveContentBySlugAction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
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
        Route::bind('slug', function (string $value) {
            return app(ResolveContentBySlugAction::class)->execute($value);
        });
    }
}
