<?php

namespace App\Application\Download\Actions;

use App\Domain\Download\Models\DownloadSection;
use App\Infrastructure\Support\Actions\AbstractSyncRepeaterAction;
use Illuminate\Database\Eloquent\Model;

class SyncDownloadSectionsAction extends AbstractSyncRepeaterAction
{
    public function __construct(
        protected SyncDownloadEntriesAction $syncDownloadEntriesAction,
    )
    {
    }

    /**
     * @return string
     */
    protected function relationName(): string
    {
        return 'downloadSections';
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

        return $this->nullableString($item['title'] ?? null) !== null;
    }

    /**
     * @param array $item
     * @param int $sortOrder
     * @param Model $owner
     * @return array|null
     */
    protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array
    {
        $title = $this->nullableString($item['title'] ?? null);

        if ($title === null) {
            return null;
        }

        return [
            'title' => $title,
            'description' => $this->nullableString($item['description'] ?? null),
            'password' => $this->nullableString($item['password'] ?? null),
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
        $this->syncEntries($record, $item);
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
        $this->syncEntries($record, $item);
    }

    /**
     * @param DownloadSection $section
     * @param array $item
     * @return void
     */
    protected function syncEntries(Model $section, array $item): void
    {
        $entries = $item['entries'] ?? [];

        if (!is_array($entries)) {
            $entries = [];
        }

        $this->syncDownloadEntriesAction->execute($section, $entries);
    }
}
