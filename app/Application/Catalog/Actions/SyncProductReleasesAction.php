<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\ProductRelease;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncProductReleasesAction extends AbstractSyncRepeaterAction
{
    public function __construct(
        private readonly SyncProductReleaseChangesAction $syncProductReleaseChangesAction
    )
    {
    }

    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'releases';
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

        return filled($item['title'] ?? null)
            || filled($item['version'] ?? null)
            || filled($item['excerpt'] ?? null)
            || filled($item['released_at'] ?? null)
            || filled($item['changes'] ?? null);
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
            'version' => $this->stringOrDefault($item['version'] ?? ''),
            'excerpt' => $this->stringOrDefault($item['excerpt'] ?? ''),
            'released_at' => $this->nullableGregorianDate($item['released_at'] ?? null),
        ];
    }

    /**
     * @param Model $record
     * @param array $item
     * @param Model $owner
     * @param int $sortOrder
     * @return void
     */
    protected function afterCreated(Model $record, array $item, Model $owner, int $sortOrder): void
    {
        $this->syncChanges($record, $item);
    }

    /**
     * @param Model $record
     * @param array $item
     * @param Model $owner
     * @param int $sortOrder
     * @return void
     */
    protected function afterUpdated(Model $record, array $item, Model $owner, int $sortOrder): void
    {
        $this->syncChanges($record, $item);
    }

    /**
     * @param Model $record
     * @param array $item
     * @return void
     */
    private function syncChanges(Model $record, array $item): void
    {
        if (!$record instanceof ProductRelease) {
            return;
        }

        $this->syncProductReleaseChangesAction->execute(
            release: $record,
            changes: $item['changes'] ?? null
        );
    }
}
