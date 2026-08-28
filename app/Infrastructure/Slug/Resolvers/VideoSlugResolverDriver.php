<?php

namespace App\Infrastructure\Slug\Resolvers;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Video\Models\VideoSlug;

class VideoSlugResolverDriver implements SlugResolverDriver
{
    public function supports(SlugScopeEnum $scope): bool { return $scope === SlugScopeEnum::VIDEO; }

    public function resolve(SlugScopeEnum $scope, string $value): ResolvedSlugDTO
    {
        $record = VideoSlug::query()->where('route_key', $value)->with('video')->first();
        if (!$record?->video) {
            return ResolvedSlugDTO::notFound($value);
        }

        $current = (string)$record->video->slug;
        return $record->is_current
            ? ResolvedSlugDTO::current($value, $current, $record->video)
            : ResolvedSlugDTO::redirect($value, $current, route('videos.show', $current), $record->video);
    }
}
