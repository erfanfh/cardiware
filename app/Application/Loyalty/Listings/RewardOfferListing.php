<?php

namespace App\Application\Loyalty\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class RewardOfferListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id',
            'title',
            'code',
            'type',
            'is_active',
            'points_cost_from',
            'points_cost_to',
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
            'points_cost' => 'points_cost',
            'sort_order' => 'sort_order',
            'starts_at' => 'starts_at',
            'ends_at' => 'ends_at',
            'redeemed_count' => 'redeemed_count',
        ];
    }

    public function defaultSort(): array
    {
        return ['sort' => 'sort_order', 'direction' => 'asc'];
    }

    protected function filterId(mixed $value): void { $this->query->where('id', $value); }
    protected function filterTitle(mixed $value): void { $this->query->where('title', 'like', '%' . $value . '%'); }
    protected function filterCode(mixed $value): void { $this->query->where('code', 'like', '%' . $value . '%'); }
    protected function filterType(mixed $value): void { $this->query->where('type', $value); }
    protected function filterIsActive(mixed $value): void { $this->query->where('is_active', (bool)$value); }
    protected function filterPointsCostFrom(mixed $value): void { $this->query->where('points_cost', '>=', $value); }
    protected function filterPointsCostTo(mixed $value): void { $this->query->where('points_cost', '<=', $value); }
    protected function filterStartsAtFrom(mixed $value): void { $this->query->whereDate('starts_at', '>=', $value); }
    protected function filterStartsAtTo(mixed $value): void { $this->query->whereDate('starts_at', '<=', $value); }
    protected function filterEndsAtFrom(mixed $value): void { $this->query->whereDate('ends_at', '>=', $value); }
    protected function filterEndsAtTo(mixed $value): void { $this->query->whereDate('ends_at', '<=', $value); }
    protected function filterCreatedAtFrom(mixed $value): void { $this->query->whereDate('created_at', '>=', $value); }
    protected function filterCreatedAtTo(mixed $value): void { $this->query->whereDate('created_at', '<=', $value); }
}
