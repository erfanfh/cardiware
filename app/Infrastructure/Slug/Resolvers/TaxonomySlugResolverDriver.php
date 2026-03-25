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
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return SlugScopeEnum::isTaxonomyScope($scope);
    }

    /**
     * Resolve a taxonomy route key from the taxonomy slug history table.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $type = SlugScopeEnum::extractTaxonomyType($scope);

        /** @var TaxonomyTermSlug|null $slugRecord */
        $slugRecord = TaxonomyTermSlug::query()
            ->where('type', $type)
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
            redirectTo: url($this->buildTaxonomyRedirectPath($type, $currentRouteKey)),
            model: $model,
        );
    }

    /**
     * Build the public redirect path for the resolved taxonomy type and route key.
     */
    private function buildTaxonomyRedirectPath(string $type, string $routeKey): string
    {
        return trim($type . '/' . $routeKey, '/');
    }
}
