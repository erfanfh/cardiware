<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;

class ProjectSlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::PROJECT;
    }

    /**
     * Determine whether the given slug exists in the project scope.
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        return ProjectSlug::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->whereKeyNot($ignoreId)
            )
            ->exists();
    }
}
