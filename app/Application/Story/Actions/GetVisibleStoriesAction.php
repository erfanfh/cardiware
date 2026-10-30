<?php

namespace App\Application\Story\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Domain\Story\Models\Story;

class GetVisibleStoriesAction
{
    public function execute(): array
    {
        return app(PublicReadCache::class)->remember(
            'visible-stories', [], [Story::class, MediaFile::class, MediaFileVariant::class],
            function (): array {
                $now = now();

                return Story::query()
                    ->with(['image.primaryVariant', 'thumbnail.primaryVariant'])
                    ->where('is_active', true)
                    ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                    ->whereHas('image', fn ($query) => $query->where('type', 'image')
                        ->where('visibility', 'public')->where('status', 'ready')->whereHas('primaryVariant'))
                    ->orderBy('sort_order')->orderBy('id')
                    ->get()
                    ->map(function (Story $story): array {
                        $image = $story->image->url;
                        $thumbnail = $story->thumbnail;
                        $thumbnailUrl = $thumbnail?->type->value === 'image' && $thumbnail?->status->value === 'ready'
                            ? $thumbnail->url : null;

                        return [
                            'id' => $story->id,
                            'title' => $story->title,
                            'subtitle' => $story->subtitle,
                            'caption' => $story->caption,
                            'image' => $image,
                            'thumb' => $thumbnailUrl ?: $image,
                            'href' => $story->link_url,
                            'linkLabel' => $story->link_label ?: 'مشاهده جزئیات',
                            'durationMs' => $story->duration_seconds * 1000,
                        ];
                    })->all();
            },
            transitions: [Story::class => ['starts_at', 'ends_at']],
        );
    }
}
