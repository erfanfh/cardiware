<?php

namespace App\Providers;

use App\Application\Seo\Resolvers\AuthorSeoResolver;
use App\Application\Seo\Resolvers\HomePageSeoResolver;
use App\Application\Seo\Resolvers\PageSeoResolver;
use App\Application\Seo\Resolvers\PostCategorySeoResolver;
use App\Application\Seo\Resolvers\PostSeoResolver;
use App\Application\Seo\Resolvers\PostTagSeoResolver;
use App\Application\Seo\Resolvers\ProjectSeoResolver;
use App\Application\Seo\Support\SeoManager;
use App\Application\Seo\Support\SeoResolverRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SeoResolverRegistry::class, function ($app) {
            return new SeoResolverRegistry([
                $app->make(HomePageSeoResolver::class),
                $app->make(ProjectSeoResolver::class),
                $app->make(PostSeoResolver::class),
                $app->make(PageSeoResolver::class),
                $app->make(PostTagSeoResolver::class),
                $app->make(PostCategorySeoResolver::class),
                $app->make(AuthorSeoResolver::class),
            ]);
        });

        $this->app->scoped(SeoManager::class, function ($app) {
            return new SeoManager(
                resolverRegistry: $app->make(SeoResolverRegistry::class),
                request: $app->make(Request::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
