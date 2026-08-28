<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Poll\Models\Poll;
use App\Domain\Slug\Enums\SlugScopeEnum;

class PollSlugChecker implements SlugScopeChecker
{
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::POLL;
    }

    public function exists(
        SlugScopeEnum $scope,
        string $slug,
        ?int $ignoreId = null,
        ?string $ignoreType = null,
    ): bool {
        return Poll::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn ($query) => $query->whereKeyNot($ignoreId),
            )
            ->exists();
    }
}
