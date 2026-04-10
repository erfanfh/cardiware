<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Domain\Project\Models\ProjectSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Model;

class ProjectSlugPersistenceDriver implements SlugPersistenceDriver
{
    /**
     * Determine whether this driver supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::PROJECT;
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
     * Persist the final slug on the project model.
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

        $model->forceFill([
            'slug' => $finalSlug,
        ]);

        $model->save();

        ProjectSlug::query()
            ->where('project_id', $model->getKey())
            ->where('is_current', true)
            ->update([
                'is_current' => false,
            ]);

        ProjectSlug::query()->updateOrCreate(
            [
                'route_key' => $finalSlug,
            ],
            [
                'project_id' => $model->getKey(),
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
