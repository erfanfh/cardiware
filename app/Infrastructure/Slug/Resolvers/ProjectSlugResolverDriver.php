<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Project\Models\Project;
use App\Domain\Slug\Enums\SlugScopeEnum;

class ProjectSlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::PROJECT;
    }

    /**
     * Resolve a project by its current slug.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        /** @var Project|null $project */
        $project = Project::query()
            ->where('slug', $value)
            ->first();

        if ($project === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        return ResolvedSlugDTO::current(
            requestedValue: $value,
            currentValue: $value,
            model: $project,
        );
    }
}
