<?php

namespace App\Application\Catalog\Actions;

use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductMedalAssignmentsAction extends AbstractSyncRepeaterAction
{
    protected function relationName(): string
    {
        return 'medalAssignments';
    }

    protected function isMeaningfulItem(mixed $item): bool
    {
        if (!is_array($item)) {
            return false;
        }

        return filled($item['product_medal_id'] ?? null);
    }

    protected function mapItemToPayload(
        array $item,
        int   $sortOrder,
        Model $owner
    ): ?array
    {
        $productMedalId = $this->toInt(
            $item['product_medal_id'] ?? null
        );

        if ($productMedalId <= 0) {
            return null;
        }

        return [
            'product_medal_id' => $productMedalId,
        ];
    }
}
