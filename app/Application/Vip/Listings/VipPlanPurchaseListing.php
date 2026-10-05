<?php

namespace App\Application\Vip\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class VipPlanPurchaseListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'user', 'order_number', 'status',
            'starts_at_from', 'starts_at_to',
            'expires_at_from', 'expires_at_to',
            'created_at_from', 'created_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'starts_at' => 'starts_at',
            'expires_at' => 'expires_at',
            'duration_days' => 'duration_days',
            'id' => 'id',
        ];
    }

    public function defaultSort(): array
    {
        return ['sort' => 'created_at', 'direction' => 'desc'];
    }

    protected function applySort(string $sort, string $direction): void
    {
        parent::applySort($sort, $direction);

        if ($sort !== 'id') {
            $this->query->orderBy('id', 'desc');
        }
    }

    protected function filterUser(mixed $value): void
    {
        $this->query->whereHas('user', function ($query) use ($value): void {
            $query->withTrashed()->where(function ($query) use ($value): void {
                $query->where('display_name', 'like', "%{$value}%")
                    ->orWhere('first_name', 'like', "%{$value}%")
                    ->orWhere('last_name', 'like', "%{$value}%")
                    ->orWhere('username', 'like', "%{$value}%")
                    ->orWhere('mobile', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%");

                if (ctype_digit((string) $value)) {
                    $query->orWhere('id', (int) $value);
                }
            });
        });
    }

    protected function filterOrderNumber(mixed $value): void
    {
        $this->query->whereHas('order', fn ($query) => $query->where('order_number', 'like', "%{$value}%"));
    }

    protected function filterStatus(mixed $value): void
    {
        $now = now();

        match ($value) {
            'active' => $this->query->whereNull('revoked_at')->where('starts_at', '<=', $now)->where('expires_at', '>', $now),
            'scheduled' => $this->query->whereNull('revoked_at')->where('starts_at', '>', $now),
            'expired' => $this->query->whereNull('revoked_at')->where('expires_at', '<=', $now),
            'revoked' => $this->query->whereNotNull('revoked_at'),
            default => null,
        };
    }

    protected function filterStartsAtFrom(mixed $value): void
    {
        $this->query->whereDate('starts_at', '>=', $value);
    }

    protected function filterStartsAtTo(mixed $value): void
    {
        $this->query->whereDate('starts_at', '<=', $value);
    }

    protected function filterExpiresAtFrom(mixed $value): void
    {
        $this->query->whereDate('expires_at', '>=', $value);
    }

    protected function filterExpiresAtTo(mixed $value): void
    {
        $this->query->whereDate('expires_at', '<=', $value);
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
