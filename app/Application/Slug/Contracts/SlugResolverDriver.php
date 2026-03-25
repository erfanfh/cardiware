<?php

namespace App\Application\Slug\Contracts;

use App\Application\Slug\DTOs\ResolvedSlugDTO;

interface SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(string $scope): bool;

    /**
     * Resolve the given route value within the provided scope.
     */
    public function resolve(string $scope, string $value): ResolvedSlugDTO;
}
