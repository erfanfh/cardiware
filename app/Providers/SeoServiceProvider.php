<?php

namespace App\Providers;

use App\Application\Seo\Actions\Sitemap\MarkSitemapsDirtyAction;
use App\Application\Seo\Contracts\StaticSitemapGeneratorInterface;
use App\Application\Seo\Generators\Sitemap\AuthorStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\CategoryStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\PageStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\PostStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\ProductStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\ProjectStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\RaffleStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\TagStaticSitemapGenerator;
use App\Application\Seo\Generators\Sitemap\VideoStaticSitemapGenerator;
use App\Application\Seo\Resolvers\AuthorSeoResolver;
use App\Application\Seo\Resolvers\AuthSeoResolver;
use App\Application\Seo\Resolvers\HomePageSeoResolver;
use App\Application\Seo\Resolvers\PageSeoResolver;
use App\Application\Seo\Resolvers\PostCategorySeoResolver;
use App\Application\Seo\Resolvers\PostListSeoResolver;
use App\Application\Seo\Resolvers\PostSeoResolver;
use App\Application\Seo\Resolvers\PostTagSeoResolver;
use App\Application\Seo\Resolvers\ProductListSeoResolver;
use App\Application\Seo\Resolvers\ProductSeoResolver;
use App\Application\Seo\Resolvers\ProjectListSeoResolver;
use App\Application\Seo\Resolvers\ProjectSeoResolver;
use App\Application\Seo\Resolvers\RaffleSeoResolver;
use App\Application\Seo\Resolvers\SearchSeoResolver;
use App\Application\Seo\Resolvers\StoreSeoResolver;
use App\Application\Seo\Resolvers\VideoSeoResolver;
use App\Application\Seo\Support\SeoManager;
use App\Application\Seo\Support\SeoResolverRegistry;
use App\Application\Seo\Support\Sitemap\SitemapStorage;
use App\Application\Seo\Support\Sitemap\StaticSitemapGeneratorRegistry;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Models\Project;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
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

                $app->make(AuthSeoResolver::class),
                $app->make(SearchSeoResolver::class),

                $app->make(PostListSeoResolver::class),
                $app->make(ProjectListSeoResolver::class),
                $app->make(ProductListSeoResolver::class),
                $app->make(RaffleSeoResolver::class),
                $app->make(VideoSeoResolver::class),

                $app->make(AuthorSeoResolver::class),
                $app->make(PostTagSeoResolver::class),
                $app->make(PostCategorySeoResolver::class),

                $app->make(StoreSeoResolver::class),
                $app->make(ProjectSeoResolver::class),
                $app->make(ProductSeoResolver::class),
                $app->make(PostSeoResolver::class),
                $app->make(PageSeoResolver::class),
            ]);
        });

        $this->app->scoped(SeoManager::class, function ($app) {
            return new SeoManager(
                resolverRegistry: $app->make(SeoResolverRegistry::class),
                request: $app->make(Request::class),
            );
        });

        $this->app->singleton(SitemapStorage::class);

        $this->app->tag([
            PostStaticSitemapGenerator::class,
            PageStaticSitemapGenerator::class,
            ProjectStaticSitemapGenerator::class,
            CategoryStaticSitemapGenerator::class,
            TagStaticSitemapGenerator::class,
            AuthorStaticSitemapGenerator::class,
            ProductStaticSitemapGenerator::class,
            RaffleStaticSitemapGenerator::class,
            VideoStaticSitemapGenerator::class,
        ], StaticSitemapGeneratorInterface::class);

        $this->app->bind(
            StaticSitemapGeneratorRegistry::class,
            fn ($app) => new StaticSitemapGeneratorRegistry(
                $app->tagged(StaticSitemapGeneratorInterface::class)
            )
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $markSitemapsDirty = fn () => app(MarkSitemapsDirtyAction::class)->execute();

        Post::saved($markSitemapsDirty);
        Post::deleted($markSitemapsDirty);
        Post::restored($markSitemapsDirty);

        Page::saved($markSitemapsDirty);
        Page::deleted($markSitemapsDirty);
        Page::restored($markSitemapsDirty);

        Project::saved($markSitemapsDirty);
        Project::deleted($markSitemapsDirty);
        Project::restored($markSitemapsDirty);

        TaxonomyTerm::saved($markSitemapsDirty);
        TaxonomyTerm::deleted($markSitemapsDirty);
        TaxonomyTerm::restored($markSitemapsDirty);

        User::saved($markSitemapsDirty);
        User::deleted($markSitemapsDirty);
        User::restored($markSitemapsDirty);

        Product::saved($markSitemapsDirty);
        Product::deleted($markSitemapsDirty);
        Product::restored($markSitemapsDirty);

        DrawCampaign::saved($markSitemapsDirty);
        DrawCampaign::deleted($markSitemapsDirty);
        DrawCampaign::restored($markSitemapsDirty);

        Video::saved($markSitemapsDirty);
        Video::deleted($markSitemapsDirty);
        Video::restored($markSitemapsDirty);
    }
}
