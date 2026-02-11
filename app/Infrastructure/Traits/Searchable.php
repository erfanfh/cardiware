<?php

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Searchable
 */
trait Searchable
{
    /**
     * Apply search filter to the query builder.
     * Search in title, slug, and description columns
     *
     * @param Builder $query The Eloquent query builder instance
     * @param string|null $search Search query string (nullable)
     * @param array $columns Array of column names to search in
     * @return Builder Query builder with search filter applied
     */
    protected function applySearch(
        Builder $query,
        ?string $search,
        array $columns
    ): Builder {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }
}
