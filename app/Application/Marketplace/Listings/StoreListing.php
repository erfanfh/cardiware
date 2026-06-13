<?php

namespace App\Application\Marketplace\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class StoreListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'title',
            'status',
            'created_at_from',
            'created_at_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'id' => 'id',
            'title' => 'title',
        ];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%' . $value . '%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }
}
