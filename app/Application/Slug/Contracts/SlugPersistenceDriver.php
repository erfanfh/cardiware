<?php

namespace App\Application\Slug\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SlugPersistenceDriver
{
    /**
     * Determine whether this driver supports the given scope.
     */
    public function supports(string $scope): bool;

    /**
     * Get the current slug value from the given model.
     */
    public function getCurrentSlug(Model $model): ?string;

    /**
     * Persist the final slug on the given model and register related history if needed.
     *
     * @return array{
     *     final_slug: string,
     *     previous_slug: ?string,
     *     changed: bool,
     *     history_recorded: bool
     * }
     */
    public function persist(Model $model, string $scope, string $finalSlug): array;
}
