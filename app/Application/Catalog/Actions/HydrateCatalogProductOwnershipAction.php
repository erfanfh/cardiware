<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Collection;

class HydrateCatalogProductOwnershipAction
{
    /**
     * @param Collection<int, Product> $products
     */
    public function execute(
        Collection $products,
        ?User      $user,
    ): void
    {
        $products->each(
            function (Product $product) use ($user): void {
                $product->setAttribute('viewer_authenticated', $user !== null);

                if ($user === null) {
                    $product->setAttribute('is_purchased', null);
                    $product->setAttribute('has_usable_access', null);

                    $product->saleItems->each(
                        function (ProductSaleItem $saleItem): void {
                            $saleItem->setAttribute('viewer_authenticated', false);
                            $saleItem->setAttribute('is_purchased', null);
                            $saleItem->setAttribute('has_usable_access', null);
                            $saleItem->setAttribute('purchase_access_id', null);
                        }
                    );

                    return;
                }

                // دسترسی امانی VIP خرید حساب نمی‌شود («خریداری‌شده» نشان داده نشود).
                $accesses = ($product->relationLoaded('purchaseAccesses')
                    ? $product->purchaseAccesses
                    : collect())
                    ->reject(static fn(PurchaseAccess $access): bool => $access->isVipBorrow())
                    ->values();

                $usableAccesses = $accesses->filter(
                    static fn(PurchaseAccess $access): bool => $access->isUsableForLauncher()
                );

                $product->setAttribute(
                    'is_purchased',
                    $accesses->isNotEmpty()
                );

                $product->setAttribute(
                    'has_usable_access',
                    $usableAccesses->isNotEmpty()
                );

                $product->saleItems->each(
                    function (ProductSaleItem $saleItem) use ($accesses): void {
                        $saleItemAccesses = $accesses->where(
                            'product_sale_item_id',
                            $saleItem->getKey()
                        );

                        $usableAccess = $saleItemAccesses->first(
                            static fn(PurchaseAccess $access): bool => $access->isUsableForLauncher()
                        );

                        $firstAccess = $usableAccess ?? $saleItemAccesses->first();

                        $saleItem->setAttribute('viewer_authenticated', true);
                        $saleItem->setAttribute('is_purchased', $saleItemAccesses->isNotEmpty());
                        $saleItem->setAttribute('has_usable_access', $usableAccess !== null);
                        $saleItem->setAttribute('purchase_access_id', $firstAccess?->getKey());
                    }
                );
            }
        );
    }
}
