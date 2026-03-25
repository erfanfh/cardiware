<?php

namespace App\Providers;

use App\Application\Slug\Services\SlugManager;
use App\Application\Slug\Services\SlugNormalizer;
use App\Application\Slug\Services\SlugSuggestionGenerator;
use App\Infrastructure\Slug\Checkers\ContentSlugChecker;
use App\Infrastructure\Slug\Checkers\ProjectSlugChecker;
use App\Infrastructure\Slug\Checkers\TaxonomySlugChecker;
use Illuminate\Support\ServiceProvider;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register slug-related services and checkers.
     */
    public function register(): void
    {
        $this->app->singleton(SlugNormalizer::class);
        $this->app->singleton(SlugSuggestionGenerator::class);

        $this->app->singleton(ContentSlugChecker::class);
        $this->app->singleton(ProjectSlugChecker::class);
        $this->app->singleton(TaxonomySlugChecker::class);

        $this->app->tag(
            [
                ContentSlugChecker::class,
                ProjectSlugChecker::class,
                TaxonomySlugChecker::class,
            ],
            'slug.scope.checkers'
        );

        $this->app->singleton(SlugManager::class, function ($app): SlugManager {
            return new SlugManager(
                checkers: $app->tagged('slug.scope.checkers'),
                normalizer: $app->make(SlugNormalizer::class),
                suggestionGenerator: $app->make(SlugSuggestionGenerator::class),
            );
        });
    }
}
