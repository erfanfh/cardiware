<?php

namespace App\Providers;

use App\Application\Seo\Providers\Sitemap\AuthorSitemapProvider;
use App\Application\Seo\Providers\Sitemap\CategorySitemapProvider;
use App\Application\Seo\Providers\Sitemap\PageSitemapProvider;
use App\Application\Seo\Providers\Sitemap\PostSitemapProvider;
use App\Application\Seo\Providers\Sitemap\TagSitemapProvider;
use App\Application\Seo\Support\Sitemap\SitemapProviderRegistry;
use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->tag([
            PostSitemapProvider::class,
            PageSitemapProvider::class,
            CategorySitemapProvider::class,
            TagSitemapProvider::class,
            AuthorSitemapProvider::class,
        ], 'seo.sitemap.providers');

        $this->app->singleton(SitemapProviderRegistry::class, function ($app) {
            return new SitemapProviderRegistry(
                $app->tagged('seo.sitemap.providers')
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
