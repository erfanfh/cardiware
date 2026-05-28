<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Enums\ProductMediaCollectionEnum;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductImageGalleryAction extends AbstractSyncRepeaterAction
{
    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'imageGallery';
    }

    /**
     * @param mixed $item
     * @return bool
     */
    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $item
     * @param int $sortOrder
     * @param Model $owner
     * @return array|null
     */
    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        return [
            'title' => $this->nullableString($item['title'] ?? null),
            'media_file_id' => $this->nullableString($item['image']['id'] ?? null),
            'collection' => ProductMediaCollectionEnum::IMAGE_GALLERY
        ];
    }
}
