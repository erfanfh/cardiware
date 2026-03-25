<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TaxonomySlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(string $scope): bool
    {
        return str_starts_with($scope, 'taxonomy:');
    }

    /**
     * Resolve a taxonomy route key from the taxonomy slug history table.
     */
    public function resolve(string $scope, string $value): ResolvedSlugDTO
    {
        $type = $this->extractTypeFromScope($scope);

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
     * Extract the taxonomy type from the given scope value.
     */
    private function extractTypeFromScope(string $scope): string
    {
        $parts = explode(':', $scope, 2);

        if (count($parts) !== 2 || trim($parts[1]) === '') {
            throw new InvalidArgumentException("Invalid taxonomy scope [{$scope}].");
        }

        return trim($parts[1]);
    }

    /**
     * Build the public redirect path for the resolved taxonomy type and route key.
     */
    private function buildTaxonomyRedirectPath(string $type, string $routeKey): string
    {
        return trim($type . '/' . $routeKey, '/');
    }
}
