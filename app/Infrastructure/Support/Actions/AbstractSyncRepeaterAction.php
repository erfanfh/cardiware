<?php

namespace App\Infrastructure\Support\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

abstract class AbstractSyncRepeaterAction
{
    /**
     * @param Model $owner
     * @param array $items
     * @return void
     */
    public function execute(Model $owner, array $items): void
    {
        $relation = $this->relation($owner);

        $items = collect($items)
            ->filter(fn(mixed $item) => $this->isMeaningfulItem($item))
            ->values();

        $existingRecords = $relation->get()->keyBy($this->primaryKeyName());
        $keptIds = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $itemId = $this->normalizeId($item[$this->itemIdKey()] ?? null);
            $payload = $this->makePayload($item, $index, $owner);

            if ($payload === null) {
                continue;
            }

            if ($itemId !== null) {
                $existingRecord = $existingRecords->get($itemId);

                if ($existingRecord !== null) {
                    $existingRecord->update($payload);
                    $keptIds[] = $existingRecord->getKey();
                    $this->afterUpdated($existingRecord, $item, $owner, $index);
                    continue;
                }
            }

            $newRecord = $relation->create($payload);
            $keptIds[] = $newRecord->getKey();
            $this->afterCreated($newRecord, $item, $owner, $index);
        }

        $this->deleteMissingRecords($relation, $keptIds, $owner, $existingRecords->all());
    }

    /**
     * @return string
     */
    abstract protected function relationName(): string;

    /**
     * @param array $item
     * @param int $sortOrder
     * @param Model $owner
     * @return array|null
     */
    abstract protected function mapItemToPayload(array $item, int $sortOrder, Model $owner): ?array;

    /**
     * @param mixed $item
     * @return bool
     */
    protected function isMeaningfulItem(mixed $item): bool
    {
        return is_array($item);
    }

    /**
     * @return string
     */
    protected function itemIdKey(): string
    {
        return 'id';
    }

    /**
     * @return string
     */
    protected function primaryKeyName(): string
    {
        return 'id';
    }

    /**
     * @return bool
     */
    protected function usesSortOrder(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function sortOrderColumn(): string
    {
        return 'sort_order';
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
    }

    /**
     * @param Model $owner
     * @return HasMany
     */
    protected function relation(Model $owner): HasMany
    {
        $relationName = $this->relationName();

        if (!method_exists($owner, $relationName)) {
            throw new InvalidArgumentException(sprintf(
                'Relation [%s] does not exist on model [%s].',
                $relationName,
                $owner::class
            ));
        }

        $relation = $owner->{$relationName}();

        if (!$relation instanceof HasMany) {
            throw new InvalidArgumentException(sprintf(
                'Relation [%s] on model [%s] must be an instance of [%s].',
                $relationName,
                $owner::class,
                HasMany::class
            ));
        }

        return $relation;
    }

    /**
     * @param array $item
     * @param int $sortOrder
     * @param Model $owner
     * @return array|null
     */
    protected function makePayload(array $item, int $sortOrder, Model $owner): ?array
    {
        $payload = $this->mapItemToPayload($item, $sortOrder, $owner);

        if ($payload === null) {
            return null;
        }

        if ($this->usesSortOrder()) {
            $payload[$this->sortOrderColumn()] = $sortOrder;
        }

        return $payload;
    }

    /**
     * @param HasMany $relation
     * @param array $keptIds
     * @param Model $owner
     * @param array $existingRecords
     * @return void
     */
    protected function deleteMissingRecords(
        HasMany $relation,
        array   $keptIds,
        Model   $owner,
        array   $existingRecords
    ): void
    {
        if (empty($keptIds)) {
            $relation->delete();
            return;
        }

        $relation->whereNotIn($this->primaryKeyName(), $keptIds)->delete();
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeId(mixed $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    /**
     * @param mixed $value
     * @param string $default
     * @return string
     */
    protected function stringOrDefault(mixed $value, string $default = ''): string
    {
        $value = $this->nullableString($value);

        return $value ?? $default;
    }

    /**
     * @param mixed $value
     * @param int $default
     * @return int
     */
    protected function toInt(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (int)$value;
    }

    /**
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @param int $default
     * @return int
     */
    protected function toClampedInt(
        mixed $value,
        int   $min,
        int   $max,
        int   $default = 0
    ): int
    {
        $value = $this->toInt($value, $default);

        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function nullableGregorianDate(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        return gregorian_date($value);
    }
}
