<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\Cache;

class TaxonomySlugCacheInvalidator
{
    public function forgetBySlug(string $taxonomy, string $routeKey): void
    {
        Cache::forget(CacheKey::taxonomySlug($taxonomy, $routeKey));
    }

    public function forgetForTerm(TaxonomyTerm $term): void
    {
        $term->slugs()
            ->pluck('route_key')
            ->each(fn($routeKey) => $this->forgetBySlug($term->taxonomy->value, $routeKey));
    }
}
