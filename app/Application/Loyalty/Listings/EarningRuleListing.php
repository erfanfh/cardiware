<?php

namespace App\Application\Loyalty\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class EarningRuleListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id',
            'title',
            'code',
            'type',
            'is_active',
            'priority',
            'starts_at_from',
            'starts_at_to',
            'ends_at_from',
            'ends_at_to',
            'created_at_from',
            'created_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'id' => 'id',
            'created_at' => 'created_at',
            'priority' => 'priority',
            'starts_at' => 'starts_at',
            'ends_at' => 'ends_at',
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

    protected function filterCode(mixed $value): void
    {
        $this->query->where('code', 'like', '%' . $value . '%');
    }

    protected function filterType(mixed $value): void
    {
        $this->query->where('type', $value);
    }

    protected function filterIsActive(mixed $value): void
    {
        $this->query->where('is_active', (bool)$value);
    }

    protected function filterPriority(mixed $value): void
    {
        $this->query->where('priority', $value);
    }

    protected function filterStartsAtFrom(mixed $value): void
    {
        $this->query->whereDate('starts_at', '>=', $value);
    }

    protected function filterStartsAtTo(mixed $value): void
    {
        $this->query->whereDate('starts_at', '<=', $value);
    }

    protected function filterEndsAtFrom(mixed $value): void
    {
        $this->query->whereDate('ends_at', '>=', $value);
    }

    protected function filterEndsAtTo(mixed $value): void
    {
        $this->query->whereDate('ends_at', '<=', $value);
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
