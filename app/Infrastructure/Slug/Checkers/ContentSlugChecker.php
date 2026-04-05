<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Content\Services\ReservedSlugService;
use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Builder;

class ContentSlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::CONTENT;
    }

    /**
     * Determine whether the given slug exists in the shared content slug registry.
     *
     * The content scope is shared between models like Post and Page.
     * When editing an existing record, both ignoreId and ignoreType should
     * ideally be provided to avoid ambiguity between different models that
     * may share the same numeric identifier.
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        if (app(ReservedSlugService::class)->isReserved($slug))
            return true;

        $query = ContentSlug::query()->where('slug', $slug);

        if ($ignoreId !== null && $ignoreType !== null) {
            $query->where(function (Builder $builder) use ($ignoreId, $ignoreType): void {
                $builder
                    ->where('sluggable_id', '!=', $ignoreId)
                    ->orWhere('sluggable_type', '!=', $ignoreType);
            });
        } elseif ($ignoreId !== null) {
            $query->where('sluggable_id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
