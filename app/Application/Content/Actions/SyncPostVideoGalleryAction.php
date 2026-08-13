<?php

namespace App\Application\Content\Actions;

use App\Domain\Content\Enums\PostMediaCollectionEnum;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncPostVideoGalleryAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'videoGallery';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null)
            || filled($item['video_url'] ?? null)
            || filled(data_get($item, 'cover.id'));
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        $coverId = $this->normalizeId(data_get($item, 'cover.id'));
        $videoUrl = $this->nullableString($item['video_url'] ?? null);

        if ($coverId === null || $videoUrl === null) {
            return null;
        }

        return [
            'title' => $this->nullableString($item['title'] ?? null),
            'media_file_id' => $coverId,
            'collection' => PostMediaCollectionEnum::VIDEO_GALLERY,
            'meta' => [
                'video_url' => $videoUrl,
            ],
        ];
    }
}
