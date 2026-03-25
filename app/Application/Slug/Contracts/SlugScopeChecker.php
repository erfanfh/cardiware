<?php

namespace App\Application\Slug\Contracts;

interface SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     *
     * @param string $scope
     * @return bool
     */
    public function supports(string $scope): bool;

    /**
     * Determine whether the given slug already exists in the provided scope.
     *
     * @param string $scope
     * @param string $slug
     * @param int|null $ignoreId
     * @param string|null $ignoreType
     * @return bool
     */
    public function exists(
        string  $scope,
        string  $slug,
        ?int    $ignoreId = null,
        ?string $ignoreType = null,
    ): bool;
}
