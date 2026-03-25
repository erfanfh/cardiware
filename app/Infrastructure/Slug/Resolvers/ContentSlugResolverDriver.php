<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Content\Models\ContentSlug;
use Illuminate\Database\Eloquent\Model;

class ContentSlugResolverDriver implements SlugResolverDriver
{
    /**
     * Determine whether this resolver supports the given scope.
     */
    public function supports(string $scope): bool
    {
        return $scope === 'content';
    }

    /**
     * Resolve a content slug from the shared content slug registry.
     */
    public function resolve(string $scope, string $value): ResolvedSlugDTO
    {
        /** @var ContentSlug|null $slugRecord */
        $slugRecord = ContentSlug::query()
            ->where('slug', $value)
            ->first();

        if ($slugRecord === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        /** @var Model|null $model */
        $model = $slugRecord->sluggable;

        if ($model === null) {
            return ResolvedSlugDTO::notFound($value);
        }

        $currentSlug = $model->getAttribute('slug');
        $currentSlugValue = is_string($currentSlug) ? $currentSlug : '';

        if ($slugRecord->is_current) {
            return ResolvedSlugDTO::current(
                requestedValue: $value,
                currentValue: $currentSlugValue,
                model: $model,
            );
        }

        return ResolvedSlugDTO::redirect(
            requestedValue: $value,
            currentValue: $currentSlugValue,
            redirectTo: url($currentSlugValue),
            model: $model,
        );
    }
}
