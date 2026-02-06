<?php

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
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
