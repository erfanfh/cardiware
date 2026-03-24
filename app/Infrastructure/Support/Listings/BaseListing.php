<?php

namespace App\Infrastructure\Support\Listings;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class BaseListing
{
    protected Builder $query;

    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * @param Builder $query
     * @param array<string, mixed> $filters
     * @return Builder
     */
    public function handle(Builder $query, array $filters = []): Builder
    {
        return $this->apply($query, $filters);
    }

    /**
     * @param Builder $query
     * @param array<string, mixed> $filters
     * @return Builder
     */
    public function apply(Builder $query, array $filters = []): Builder
    {
        $this->query = $query;
        $this->filters = $filters;

        $this->applyDefinedFilters();
        $this->applySorting();

        return $this->query;
    }

    protected function applyDefinedFilters(): void
    {
        foreach ($this->filters() as $filterKey) {
            $value = $this->filters[$filterKey] ?? null;

            if ($this->isBlank($value)) {
                continue;
            }

            $method = $this->resolveFilterMethodName($filterKey);

            if (!method_exists($this, $method)) {
                continue;
            }

            $this->{$method}($value);
        }
    }

    protected function applySorting(): void
    {
        $sort = $this->filters['sort'] ?? null;
        $direction = $this->filters['direction'] ?? null;

        if (!is_string($sort) || !$this->isAllowedSort($sort)) {
            $this->applyDefaultSort();

            return;
        }

        $this->applySort($sort, $this->normalizeDirection($direction));
    }

    protected function applySort(string $sort, string $direction): void
    {
        $column = $this->allowedSorts()[$sort] ?? null;

        if (!is_string($column) || $column === '') {
            return;
        }

        $this->query->orderBy($column, $direction);
    }

    protected function applyDefaultSort(): void
    {
        $defaultSort = $this->defaultSort();

        $sort = $defaultSort['sort'] ?? null;
        $direction = $this->normalizeDirection($defaultSort['direction'] ?? 'desc');

        if (!is_string($sort) || !$this->isAllowedSort($sort)) {
            return;
        }

        $this->applySort($sort, $direction);
    }

    protected function resolveFilterMethodName(string $key): string
    {
        return 'filter' . Str::studly($key);
    }

    protected function isAllowedSort(string $sort): bool
    {
        return array_key_exists($sort, $this->allowedSorts());
    }

    /**
     * @param mixed $direction
     * @return 'asc'|'desc'
     */
    protected function normalizeDirection(mixed $direction): string
    {
        return strtolower((string)$direction) === 'asc' ? 'asc' : 'desc';
    }

    protected function isBlank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    abstract public function filters(): array;

    /**
     * @return array<string, string>
     */
    public function sortable(): array
    {
        return $this->allowedSorts();
    }

    /**
     * @return array<string, string>
     */
    abstract protected function allowedSorts(): array;

    /**
     * @return array{sort: string, direction?: 'asc'|'desc'}
     */
    protected function defaultSort(): array
    {
        return [
            'sort' => array_values($this->allowedSorts())[0] ?? null,
            'direction' => 'desc',
        ];
    }
}
