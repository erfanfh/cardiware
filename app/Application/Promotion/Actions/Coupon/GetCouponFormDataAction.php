<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetCouponFormDataAction
{
    public function execute(?Coupon $coupon = null): array
    {
        if (!$coupon) {
            return $this->emptyData();
        }

        $coupon->loadMissing([
            'users',
            'targets',
        ]);

        $selectedProductIds = $this->targetIds($coupon, Product::class);
        $selectedStoreId = $this->targetIds($coupon, Store::class)[0] ?? null;

        return [
            'coupon' => $coupon,

            'selectedUserIds' => $coupon->users
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->values()
                ->all(),

            'selectedUsers' => $coupon->users
                ->map(fn(User $user) => [
                    'value' => $user->id,
                    'label' => $this->userLabel($user),
                ])
                ->values()
                ->all(),

            'selectedProductIds' => $selectedProductIds,
            'selectedProducts' => $this->selectedProducts($selectedProductIds),

            'selectedStoreId' => $selectedStoreId,
            'selectedStore' => $this->selectedStore($selectedStoreId),
        ];
    }

    private function emptyData(): array
    {
        return [
            'coupon' => null,

            'selectedUserIds' => [],
            'selectedUsers' => [],

            'selectedProductIds' => [],
            'selectedProducts' => [],

            'selectedStoreId' => null,
            'selectedStore' => [],
        ];
    }

    private function targetIds(Coupon $coupon, string $targetClass): array
    {
        $morphAlias = Relation::getMorphAlias($targetClass);

        return $coupon->targets
            ->filter(fn($target) => in_array($target->target_type, [$morphAlias, $targetClass], true))
            ->pluck('target_id')
            ->map(fn($id) => (int)$id)
            ->values()
            ->all();
    }

    private function selectedProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return Product::query()
            ->whereKey($productIds)
            ->get(['id', 'title'])
            ->sortBy(fn(Product $product) => array_search($product->id, $productIds, true))
            ->map(fn(Product $product) => [
                'value' => $product->id,
                'label' => "$product->title ($product->id)",
            ])
            ->values()
            ->all();
    }

    private function selectedStore(?int $storeId): array
    {
        if (!$storeId) {
            return [];
        }

        $store = Store::query()
            ->whereKey($storeId)
            ->first(['id', 'title']);

        if (!$store) {
            return [];
        }

        return [
            [
                'value' => $store->id,
                'label' => "$store->title ($store->id)",
            ],
        ];
    }

    private function userLabel(User $user): string
    {
        $email = $user->email ?: 'بدون ایمیل';

        return "{$user->display_name} ({$user->mobile} | {$email})";
    }
}
