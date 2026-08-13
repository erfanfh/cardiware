<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Project\Models\ProjectSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Model;

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
     * Resolve a project slug from the project slug history table.
     */
    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $projectSlug = ProjectSlug::query()
            ->where('route_key', $value)
            ->first();

        if ($projectSlug === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        /** @var Model|null $model */
        $model = $projectSlug->project;

        if ($model === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentSlug = $model->getAttribute('slug');
        $currentSlugValue = is_string($currentSlug) ? $currentSlug : '';

        if ($projectSlug->is_current) {
            return ResolvedSlugDTO::current(
                requestedValue: $value,
                currentValue: $currentSlugValue,
                model: $model,
            );
        }

        return ResolvedSlugDTO::redirect(
            requestedValue: $value,
            currentValue: $currentSlugValue,
            redirectTo: route('projects.detail', $currentSlugValue),
            model: $model,
        );
    }
}
