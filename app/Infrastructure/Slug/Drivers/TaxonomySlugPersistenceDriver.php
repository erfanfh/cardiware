<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Infrastructure\Slug\Services\TaxonomyRouteKeyBuilder;
use Illuminate\Database\Eloquent\Model;

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
    public function supports(SlugScopeEnum $scope): bool
    {
        return SlugScopeEnum::isTaxonomyScope($scope);
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
    public function persist(Model $model, SlugScopeEnum $scope, string $finalSlug): array
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

        $type = SlugScopeEnum::extractTaxonomyType($scope);
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
}
