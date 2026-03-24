<?php

namespace App\Application\Project\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProjectAdsAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'ads';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null)
            || filled($item['link'] ?? null);
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        return [
            'title' => $this->stringOrDefault($item['title'] ?? null),
            'link' => $this->stringOrDefault($item['link'] ?? null),
        ];
    }
}
