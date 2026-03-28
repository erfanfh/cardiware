<?php

namespace App\Application\Slug\Contracts;

use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;

interface SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool;

    /**
     * Resolve the given route value within the provided scope.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO;
}
