<?php

namespace App\Application\Project\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProjectFeaturesAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'features';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null)
            || filled($item['subtitle'] ?? null);
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        return [
            'title' => $this->stringOrDefault($item['title'] ?? null),
            'subtitle' => $this->nullableString($item['subtitle'] ?? null),
        ];
    }
}
