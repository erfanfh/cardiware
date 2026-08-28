<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Slug\Enums\SlugScopeEnum;

class RaffleSlugChecker implements SlugScopeChecker
{
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::RAFFLE;
    }

    public function exists(
        SlugScopeEnum $scope,
        string $slug,
        ?int $ignoreId = null,
        ?string $ignoreType = null,
    ): bool {
        return DrawCampaign::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn ($query) => $query->whereKeyNot($ignoreId),
            )
            ->exists();
    }
}
