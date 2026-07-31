<?php

namespace App\Application\Catalog\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductActionLinksAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'actionLinks';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['title'] ?? null) || filled($item['url'] ?? null);
    }

    protected function mapItemToPayload(
        array $item,
        int   $sortOrder,
        Model $owner
    ): ?array
    {
        $title = $this->nullableString(
            $item['title'] ?? null
        );

        $url = $this->nullableString(
            $item['url'] ?? null
        );

        if ($title === null || $url === null) {
            return null;
        }

        return [
            'title' => $title,
            'url' => $url,

            'open_in_new_tab' => filter_var(
                $item['open_in_new_tab'] ?? true,
                FILTER_VALIDATE_BOOLEAN
            ),
        ];
    }
}
