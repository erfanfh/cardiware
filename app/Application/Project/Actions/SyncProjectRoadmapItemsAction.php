<?php

namespace App\Application\Project\Actions;

use App\Domain\Project\Enums\RoadmapItemStatusEnum;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProjectRoadmapItemsAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'roadmapItems';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null)
            || filled($item['excerpt'] ?? null);
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        $status = $item['status'] ?? RoadmapItemStatusEnum::INPROGRESS->value;

        return [
            'title' => $this->stringOrDefault($item['title'] ?? null),
            'status' => $status,
            'progress_percent' => $this->toClampedInt(
                $item['progress_percent'] ?? 0,
                0,
                100,
                0
            ),
            'excerpt' => $this->nullableString($item['excerpt'] ?? null),
        ];
    }
}
