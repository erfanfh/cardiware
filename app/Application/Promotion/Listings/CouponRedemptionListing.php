<?php

namespace App\Application\Promotion\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class CouponRedemptionListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'user',
            'order_number',
            'discount_from',
            'discount_to',
            'redeemed_at_from',
            'redeemed_at_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'redeemed_at' => 'redeemed_at',
            'discount_amount' => 'discount_amount',
            'order_subtotal' => 'order_subtotal',
            'id' => 'id',
        ];
    }

    protected function filterUser(mixed $value): void
    {
        $this->query->whereHas('user', function ($query) use ($value): void {
            $query
                ->withTrashed()
                ->where(function ($query) use ($value): void {
                    $query
                        ->where('display_name', 'like', "%{$value}%")
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
        $this->query->whereHas('order', function ($query) use ($value): void {
            $query->where('order_number', 'like', "%{$value}%");
        });
    }

    protected function filterDiscountFrom(mixed $value): void
    {
        $this->query->where('discount_amount', '>=', $value);
    }

    protected function filterDiscountTo(mixed $value): void
    {
        $this->query->where('discount_amount', '<=', $value);
    }

    protected function filterRedeemedAtFrom(mixed $value): void
    {
        $this->query->whereDate('redeemed_at', '>=', $value);
    }

    protected function filterRedeemedAtTo(mixed $value): void
    {
        $this->query->whereDate('redeemed_at', '<=', $value);
    }
}
