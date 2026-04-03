<?php

namespace App\Application\Download\Actions;

use App\Domain\Download\Enums\DownloadEntryTypeEnum;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncDownloadEntriesAction extends AbstractSyncRepeaterAction
{
    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'entries';
    }

    /**
     * @param mixed $item
     * @return bool
     */
    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item))
            return false;

        $type = $item['type'] ?? null;

        return in_array($type, [
            DownloadEntryTypeEnum::LINK->value,
            DownloadEntryTypeEnum::TEXT->value,
        ], true);
    }

    /**
     * @param array $item
     * @param int $sortOrder
     * @param Model $owner
     * @return array|null
     */
    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        $type = $item['type'] ?? null;
        $title = $this->stringOrDefault($item['title'] ?? '');
        $content = $this->stringOrDefault($item['content'] ?? '');

        if (!in_array($type, [
            DownloadEntryTypeEnum::LINK->value,
            DownloadEntryTypeEnum::TEXT->value,
        ], true)) {
            return null;
        }

        return [
            'type' => $type,
            'title' => $title,
            'content' => $content,
        ];
    }
}
