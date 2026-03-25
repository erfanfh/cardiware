<?php

namespace App\Providers;

use App\Application\Slug\Services\SlugAssignmentManager;
use App\Application\Slug\Services\SlugManager;
use App\Application\Slug\Services\SlugNormalizer;
use App\Application\Slug\Services\SlugSuggestionGenerator;
use App\Infrastructure\Slug\Checkers\ContentSlugChecker;
use App\Infrastructure\Slug\Checkers\ProjectSlugChecker;
use App\Infrastructure\Slug\Checkers\TaxonomySlugChecker;
use App\Infrastructure\Slug\Drivers\ContentSlugPersistenceDriver;
use App\Infrastructure\Slug\Drivers\ProjectSlugPersistenceDriver;
use App\Infrastructure\Slug\Drivers\TaxonomySlugPersistenceDriver;
use App\Infrastructure\Slug\Services\TaxonomyRouteKeyBuilder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register slug-related services, checkers, and persistence drivers.
     */
    public function register(): void
    {
        $this->app->singleton(SlugNormalizer::class);
        $this->app->singleton(SlugSuggestionGenerator::class);
        $this->app->singleton(TaxonomyRouteKeyBuilder::class);

        $this->app->singleton(ContentSlugChecker::class);
        $this->app->singleton(ProjectSlugChecker::class);
        $this->app->singleton(TaxonomySlugChecker::class);

        $this->app->singleton(ContentSlugPersistenceDriver::class);
        $this->app->singleton(ProjectSlugPersistenceDriver::class);
        $this->app->singleton(TaxonomySlugPersistenceDriver::class);

        $this->app->tag(
            [
                ContentSlugChecker::class,
                ProjectSlugChecker::class,
                TaxonomySlugChecker::class,
            ],
            'slug.scope.checkers'
        );

        $this->app->tag(
            [
                ContentSlugPersistenceDriver::class,
                ProjectSlugPersistenceDriver::class,
                TaxonomySlugPersistenceDriver::class,
            ],
            'slug.persistence.drivers'
        );

        $this->app->singleton(SlugManager::class, function ($app): SlugManager {
            return new SlugManager(
                checkers: $app->tagged('slug.scope.checkers'),
                normalizer: $app->make(SlugNormalizer::class),
                suggestionGenerator: $app->make(SlugSuggestionGenerator::class),
            );
        });

        $this->app->singleton(SlugAssignmentManager::class, function ($app): SlugAssignmentManager {
            return new SlugAssignmentManager(
                checkers: $app->tagged('slug.scope.checkers'),
                drivers: $app->tagged('slug.persistence.drivers'),
                normalizer: $app->make(SlugNormalizer::class),
                suggestionGenerator: $app->make(SlugSuggestionGenerator::class),
                db: $app->make(ConnectionInterface::class),
            );
        });
    }
}
