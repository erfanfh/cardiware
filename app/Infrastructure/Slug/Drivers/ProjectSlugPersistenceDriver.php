<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use Illuminate\Database\Eloquent\Model;

class ProjectSlugPersistenceDriver implements SlugPersistenceDriver
{
    /**
     * Determine whether this driver supports the given scope.
     */
    public function supports(string $scope): bool
    {
        return $scope === 'project';
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

        $model->forceFill([
            'slug' => $finalSlug,
        ]);

        $model->save();

        return [
            'final_slug' => $finalSlug,
            'previous_slug' => $previousSlug,
            'changed' => true,
            'history_recorded' => false,
        ];
    }
}
