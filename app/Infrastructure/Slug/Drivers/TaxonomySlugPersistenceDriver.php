<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Infrastructure\Slug\Services\TaxonomyRouteKeyBuilder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TaxonomySlugPersistenceDriver implements SlugPersistenceDriver
{
    public function __construct(
        private readonly TaxonomyRouteKeyBuilder $routeKeyBuilder,
    )
    {
    }

    /**
     * Determine whether this driver supports the given scope.
     */
    public function supports(string $scope): bool
    {
        return str_starts_with($scope, 'taxonomy:');
    }

    /**
     * Get the current slug value from the model.
     */
    public function getCurrentSlug(Model $model): ?string
    {
        $slug = $model->getAttribute('slug');

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    /**
     * Persist the final slug on the taxonomy model and register route key history.
     *
     * @return array{
     *     final_slug: string,
     *     previous_slug: ?string,
     *     changed: bool,
     *     history_recorded: bool
     * }
     */
    public function persist(Model $model, string $scope, string $finalSlug): array
    {
        $previousSlug = $this->getCurrentSlug($model);

        if ($previousSlug === $finalSlug) {
            return [
                'final_slug' => $finalSlug,
                'previous_slug' => $previousSlug,
                'changed' => false,
                'history_recorded' => false,
            ];
        }

        $type = $this->extractTypeFromScope($scope);
        $routeKey = $this->routeKeyBuilder->build($model, $finalSlug);

        $model->forceFill([
            'slug' => $finalSlug,
            'path' => $routeKey,
        ]);

        $model->save();

        TaxonomyTermSlug::query()
            ->where('taxonomy_term_id', $model->getKey())
            ->where('is_current', true)
            ->update([
                'is_current' => false,
            ]);

        TaxonomyTermSlug::query()->updateOrCreate(
            [
                'type' => $type,
                'route_key' => $routeKey,
            ],
            [
                'taxonomy_term_id' => $model->getKey(),
                'is_current' => true,
            ]
        );

        return [
            'final_slug' => $finalSlug,
            'previous_slug' => $previousSlug,
            'changed' => true,
            'history_recorded' => true,
        ];
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
}
