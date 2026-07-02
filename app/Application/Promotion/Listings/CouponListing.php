<?php

namespace App\Application\Promotion\Listings;

use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Models\Store;
use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Relations\Relation;

class CouponListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'title',
            'code',
            'status',
            'type',
            'scope',
            'user_id',
            'product_id',
            'store_id',
            'starts_at_from',
            'starts_at_to',
            'expires_at_from',
            'expires_at_to',
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
            'id' => 'id',
            'created_at' => 'created_at',
            'starts_at' => 'starts_at',
            'expires_at' => 'expires_at',
            'usage_limit' => 'usage_limit',
            'used_count' => 'used_count',
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

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterType(mixed $value): void
    {
        $this->query->where('type', $value);
    }

    protected function filterScope(mixed $value): void
    {
        $this->query->where('scope', $value);
    }

    protected function filterUserId(mixed $value): void
    {
        $this->query->whereHas('users', function ($query) use ($value) {
            $query->where('users.id', $value);
        });
    }

    protected function filterProductId(mixed $value): void
    {
        $this->query->whereHas('targets', function ($query) use ($value) {
            $query
                ->whereIn('target_type', [
                    Product::class,
                    Relation::getMorphAlias(Product::class),
                ])
                ->where('target_id', $value);
        });
    }

    protected function filterStoreId(mixed $value): void
    {
        $this->query->whereHas('targets', function ($query) use ($value) {
            $query
                ->whereIn('target_type', [
                    Store::class,
                    Relation::getMorphAlias(Store::class),
                ])
                ->where('target_id', $value);
        });
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
