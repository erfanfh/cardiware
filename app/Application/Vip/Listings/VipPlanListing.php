<?php

namespace App\Application\Vip\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class VipPlanListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id', 'title', 'code', 'is_active',
            'duration_days_min', 'duration_days_max',
            'price_toman_min', 'price_toman_max',
            'created_at_from', 'created_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'sort_order' => 'sort_order',
            'duration_days' => 'duration_days',
            'price_toman' => 'price_toman',
            'created_at' => 'created_at',
            'id' => 'id',
        ];
    }

    public function defaultSort(): array
    {
        return ['sort' => 'sort_order', 'direction' => 'asc'];
    }

    protected function applySort(string $sort, string $direction): void
    {
        parent::applySort($sort, $direction);

        if ($sort !== 'id') {
            $this->query->orderBy('id');
        }
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%'.$value.'%');
    }

    protected function filterCode(mixed $value): void
    {
        $this->query->where('code', 'like', '%'.$value.'%');
    }

    protected function filterIsActive(mixed $value): void
    {
        $this->query->where('is_active', (bool) $value);
    }

    protected function filterDurationDaysMin(mixed $value): void
    {
        $this->query->where('duration_days', '>=', $value);
    }

    protected function filterDurationDaysMax(mixed $value): void
    {
        $this->query->where('duration_days', '<=', $value);
    }

    protected function filterPriceTomanMin(mixed $value): void
    {
        $this->query->where('price_toman', '>=', $value);
    }

    protected function filterPriceTomanMax(mixed $value): void
    {
        $this->query->where('price_toman', '<=', $value);
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
