<?php

namespace App\Application\Commerce\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class OrderListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'order_number',
            'user',
            'status',
            'total_from',
            'total_to',
            'date_from',
            'date_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
        ];
    }

    protected function filterOrderNumber(mixed $value): void
    {
        $this->query->where('order_number', 'like', '%' . $value . '%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterTotalFrom(mixed $value): void
    {
        $this->query->where('total_amount', '>=', $value);
    }

    protected function filterTotalTo(mixed $value): void
    {
        $this->query->where('total_amount', '<=', $value);
    }

    protected function filterDateFrom(mixed $value): void
    {
        $this->query->whereDate('orders.created_at', '>=', $value);
    }

    protected function filterDateTo(mixed $value): void
    {
        $this->query->whereDate('orders.created_at', '<=', $value);
    }

    protected function filterUser(mixed $value): void
    {
        $this->query
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where(function ($query) use ($value) {
                $query->where('users.first_name', 'like', "%{$value}%")
                    ->orWhere('users.last_name', 'like', "%{$value}%")
                    ->orWhere('users.email', 'like', "%{$value}%")
                    ->orWhere('users.mobile', 'like', "%{$value}%");
            })
            ->select('orders.*', 'users.first_name', 'users.last_name', 'users.email', 'users.mobile');
    }
}
