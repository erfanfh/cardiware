<?php

namespace App\Application\Catalog\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductRequirementsAction extends AbstractSyncRepeaterAction
{
    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'requirements';
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
            'title' => $this->stringOrDefault($item['title'] ?? ''),
            'value' => $this->stringOrDefault($item['value'] ?? ''),
        ];
    }
}
