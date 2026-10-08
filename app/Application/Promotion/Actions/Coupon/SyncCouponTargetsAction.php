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
        $scope = $data['scope'] instanceof CouponScopeEnum
            ? $data['scope']->value : $data['scope'];
        $desired = [];

        if ($scope === CouponScopeEnum::PRODUCTS->value) {
            foreach (collect($data['product_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique() as $id) {
                $desired[Relation::getMorphAlias(Product::class).':'.$id] = [
                    'target_type' => Relation::getMorphAlias(Product::class),
                    'target_id' => $id,
                ];
            }
        } elseif ($scope === CouponScopeEnum::STORE->value && filled($data['store_id'] ?? null)) {
            $id = (int) $data['store_id'];
            $desired[Relation::getMorphAlias(Store::class).':'.$id] = [
                'target_type' => Relation::getMorphAlias(Store::class),
                'target_id' => $id,
            ];
        }

        foreach ($coupon->targets()->get() as $target) {
            $key = $target->target_type.':'.$target->target_id;
            if (isset($desired[$key])) {
                unset($desired[$key]);
            } else {
                $target->delete();
            }
        }

        foreach ($desired as $target) {
            $coupon->targets()->create($target);
        }
    }
}
