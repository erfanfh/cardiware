<?php

namespace App\Infrastructure\Slug\Checkers;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Video\Models\VideoSlug;

class VideoSlugChecker implements SlugScopeChecker
{
    public function supports(SlugScopeEnum $scope): bool { return $scope === SlugScopeEnum::VIDEO; }

    public function exists(SlugScopeEnum $scope, string $slug, ?int $ignoreId = null, ?string $ignoreType = null): bool
    {
        return VideoSlug::query()->where('route_key', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('video_id', '!=', $ignoreId))
            ->exists();
    }
}
