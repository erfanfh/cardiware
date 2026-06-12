<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Marketplace\Models\StoreSlug;
use App\Domain\Slug\Enums\SlugScopeEnum;

class StoreSlugChecker implements SlugScopeChecker
{
    /**
     * Determine whether this checker supports the given scope.
     */
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::STORE;
    }

    /**
     * Determine whether the given slug exists in the store scope.
     */
    public function exists(
        SlugScopeEnum $scope,
        string        $slug,
        ?int          $ignoreId = null,
        ?string       $ignoreType = null,
    ): bool
    {
        return StoreSlug::query()
            ->where('route_key', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('store_id', '!=', $ignoreId)
            )
            ->exists();
    }
}
