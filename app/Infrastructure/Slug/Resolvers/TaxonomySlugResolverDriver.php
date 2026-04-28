<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Support\Facades\Cache;

class TaxonomySlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     *
     * @param SlugScopeEnum $scope
     * @return bool
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return SlugScopeEnum::isTaxonomyScope($scope);
    }

    /**
     * Resolve a taxonomy route key from the taxonomy slug history table.
     *
     * @param SlugScopeEnum $scope
     * @param string $value
     * @return ResolvedSlugDTO
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $taxonomy = SlugScopeEnum::extractTaxonomyEnum($scope);

        $cached = Cache::rememberForever(
            CacheKey::taxonomySlug($taxonomy->value, $value),
            fn() => $this->resolveFromDatabase($taxonomy, $value)
        );

        return $this->mapCachedResultToDTO($taxonomy, $value, $cached);
    }

    private function resolveFromDatabase($taxonomy, string $value): array
    {
        $slugRecord = TaxonomyTermSlug::query()
            ->where('taxonomy', $taxonomy->value)
            ->where('route_key', $value)
            ->first();

        if ($slugRecord === null) {
            return [
                'type' => 'not_found',
            ];
        }

        $model = $slugRecord->term;

        if ($model === null) {
            return [
                'type' => 'not_found',
            ];
        }

        $currentValue = $model->getAttribute('path');
        $currentRouteKey = is_string($currentValue) ? $currentValue : '';

        return [
            'type' => $slugRecord->is_current ? 'current' : 'redirect',
            'term_id' => $model->getKey(),
            'current_route_key' => $currentRouteKey,
        ];
    }

    private function mapCachedResultToDTO($taxonomy, string $value, array $cached): ResolvedSlugDTO
    {
        if (($cached['type'] ?? null) === 'not_found') {
            return ResolvedSlugDTO::notFound($value);
        }

        $termId = $cached['term_id'] ?? null;

        if (!is_numeric($termId)) {
            return ResolvedSlugDTO::notFound($value);
        }

        $model = TaxonomyTerm::query()->find((int)$termId);

        if (!$model) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentRouteKey = (string)($cached['current_route_key'] ?? '');

        if (($cached['type'] ?? null) === 'current') {
            return ResolvedSlugDTO::current(
                requestedValue: $value,
                currentValue: $currentRouteKey,
                model: $model,
            );
        }

        return ResolvedSlugDTO::redirect(
            requestedValue: $value,
            currentValue: $currentRouteKey,
            redirectTo: route(
                $taxonomy->archiveRouteName(),
                ['path' => $currentRouteKey]
            ),
            model: $model,
        );
    }
}
