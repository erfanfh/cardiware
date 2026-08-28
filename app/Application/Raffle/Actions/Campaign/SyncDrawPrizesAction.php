<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncDrawPrizesAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'prizes';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        return is_array($item) && filled($item['title'] ?? null);
    }

    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        return [
            'title' => $this->stringOrDefault($item['title'] ?? null),
            'description' => $this->nullableString($item['description'] ?? null),
            'delivery_description' => $this->nullableString($item['delivery_description'] ?? null),
            'estimated_value' => filled($item['estimated_value'] ?? null) ? (int)$item['estimated_value'] : null,
            'media_file_id' => filled(data_get($item, 'media.id')) ? (int)data_get($item, 'media.id') : null,
            'quantity' => max(1, (int)($item['quantity'] ?? 1)),
            'reserve_winners_count' => filled($item['reserve_winners_count'] ?? null)
                ? max(0, (int)$item['reserve_winners_count'])
                : null,
        ];
    }
}
