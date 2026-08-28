<?php

namespace App\Application\Loyalty\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class PointLedgerEntryListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'type',
            'entry_direction',
            'reason',
            'amount_from',
            'amount_to',
            'occurred_at_from',
            'occurred_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'occurred_at' => 'occurred_at',
            'amount' => 'amount',
            'created_at' => 'created_at',
        ];
    }

    public function defaultSort(): array
    {
        return [
            'sort' => 'occurred_at',
            'direction' => 'desc',
        ];
    }

    public function perPage(): int
    {
        return 20;
    }

    protected function filterType(mixed $value): void
    {
        $this->query->where('type', $value);
    }

    protected function filterEntryDirection(mixed $value): void
    {
        $this->query->where('direction', $value);
    }

    protected function filterReason(mixed $value): void
    {
        $this->query->where('reason', 'like', '%' . trim((string)$value) . '%');
    }

    protected function filterAmountFrom(mixed $value): void
    {
        $this->query->where('amount', '>=', (int)$value);
    }

    protected function filterAmountTo(mixed $value): void
    {
        $this->query->where('amount', '<=', (int)$value);
    }

    protected function filterOccurredAtFrom(mixed $value): void
    {
        $this->query->whereDate('occurred_at', '>=', $value);
    }

    protected function filterOccurredAtTo(mixed $value): void
    {
        $this->query->whereDate('occurred_at', '<=', $value);
    }
}
