<?php

namespace App\Application\Commerce\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class AbandonedCartListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'search',
            'customer_type',
            'total_from',
            'total_to',
            'updated_from',
            'updated_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'updated_at' => 'carts.updated_at',
            'created_at' => 'carts.created_at',
            'total_amount' => 'carts.total_amount',
        ];
    }

    public function defaultSort(): array
    {
        return [
            'sort' => 'updated_at',
            'direction' => 'desc',
        ];
    }

    public function perPage(): int
    {
        return 20;
    }

    protected function filterSearch(mixed $value): void
    {
        $value = trim((string) $value);

        $this->query->where(function (Builder $query) use ($value): void {
            if (ctype_digit($value)) {
                $query->orWhere('carts.id', (int) $value);
            }

            $query
                ->orWhere('carts.session_id', 'like', "%{$value}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($value): void {
                    $userQuery
                        ->withTrashed()
                        ->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('display_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%");
                });
        });
    }

    protected function filterCustomerType(mixed $value): void
    {
        match ((string) $value) {
            'user' => $this->query->whereNotNull('carts.user_id'),
            'guest' => $this->query->whereNull('carts.user_id'),
            default => null,
        };
    }

    protected function filterTotalFrom(mixed $value): void
    {
        $this->query->where('carts.total_amount', '>=', (int) $value);
    }

    protected function filterTotalTo(mixed $value): void
    {
        $this->query->where('carts.total_amount', '<=', (int) $value);
    }

    protected function filterUpdatedFrom(mixed $value): void
    {
        $this->query->whereDate('carts.updated_at', '>=', $value);
    }

    protected function filterUpdatedTo(mixed $value): void
    {
        $this->query->whereDate('carts.updated_at', '<=', $value);
    }
}
