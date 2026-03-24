<?php

namespace App\Application\Project\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProjectChangelogsAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'changelogs';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null)
            || filled($item['tags'] ?? null)
            || filled($item['body'] ?? null)
            || filled($item['published_at'] ?? null);
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        return [
            'title' => $this->stringOrDefault($item['title'] ?? null),
            'tags' => $this->nullableString($item['tags'] ?? null),
            'body' => $this->nullableString($item['body'] ?? null),
            'published_at' => $this->nullableGregorianDate($item['published_at'] ?? null),
        ];
    }
}
