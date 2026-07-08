<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\Eloquent\Relations\Relation;

class SyncCouponTargetsAction
{
    public function execute(Coupon $coupon, array $data): void
    {
        $coupon->targets()->delete();

        $scope = $data['scope'] instanceof CouponScopeEnum
            ? $data['scope']->value
            : $data['scope'];

        if ($scope === CouponScopeEnum::ORDER->value) {
            return;
        }

        if ($scope === CouponScopeEnum::PRODUCTS->value) {
            $this->syncProducts($coupon, $data['product_ids'] ?? []);

            return;
        }

        if ($scope === CouponScopeEnum::STORE->value) {
            $this->syncStore($coupon, $data['store_id'] ?? null);
        }
    }

    private function syncProducts(Coupon $coupon, array $productIds): void
    {
        $productIds = collect($productIds)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $now = now();
        $targetType = Relation::getMorphAlias(Product::class);

        $rows = $productIds->map(fn(int $productId) => [
            'coupon_id' => $coupon->id,
            'target_type' => $targetType,
            'target_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        $coupon->targets()->insert($rows);
    }

    private function syncStore(Coupon $coupon, mixed $storeId): void
    {
        if (blank($storeId)) {
            return;
        }

        $coupon->targets()->create([
            'target_type' => Relation::getMorphAlias(Store::class),
            'target_id' => (int)$storeId,
        ]);
    }
}
