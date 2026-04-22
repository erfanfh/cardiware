<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Database\Eloquent\Model;

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

        /** @var TaxonomyTermSlug|null $slugRecord */
        $slugRecord = TaxonomyTermSlug::query()
            ->where('taxonomy', $taxonomy->value)
            ->where('route_key', $value)
            ->first();

        if ($slugRecord === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        /** @var Model|null $model */
        $model = $slugRecord->term;

        if ($model === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentValue = $model->getAttribute('path');
        $currentRouteKey = is_string($currentValue) ? $currentValue : '';

        if ($slugRecord->is_current) {
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
