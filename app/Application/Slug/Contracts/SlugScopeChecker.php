<?php

namespace App\Application\Slug\Contracts;

use App\Domain\Slug\Enums\SlugScopeEnum;

interface SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     *
     * @param SlugScopeEnum $scope
     * @return bool
     */
    public function supports(SlugScopeEnum $scope): bool;

    /**
     * Determine whether the given slug already exists in the provided scope.
     *
     * @param SlugScopeEnum $scope
     * @param string $slug
     * @param int|null $ignoreId
     * @param string|null $ignoreType
     * @return bool
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool;
}
