<?php

namespace App\Application\Catalog\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductFaqsAction extends AbstractSyncRepeaterAction
{
    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'faqs';
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
            'question' => $this->stringOrDefault($item['question'] ?? ''),
            'answer' => $this->stringOrDefault($item['answer'] ?? ''),
        ];
    }
}
