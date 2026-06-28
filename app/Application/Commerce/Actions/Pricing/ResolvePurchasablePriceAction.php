<?php

namespace App\Application\Commerce\Actions\Pricing;

use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\DTOs\PurchasablePriceDTO;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use Illuminate\Database\Eloquent\Model;

class ResolvePurchasablePriceAction
{
    /**
     * @param Purchasable&Model $purchasable
     */
    public function execute(Purchasable&Model $purchasable): PurchasablePriceDTO
    {
        if ($purchasable instanceof ProductSaleItem) {
            return $this->resolveProductSaleItemPrice($purchasable);
        }

        $price = (int)$purchasable->getPurchasablePrice();

        return new PurchasablePriceDTO(
            basePrice: $price,
            finalPrice: $price,
        );
    }

    private function resolveProductSaleItemPrice(ProductSaleItem $saleItem): PurchasablePriceDTO
    {
        $basePrice = max(0, (int)$saleItem->base_price);

        $saleItem->loadMissing('discount');

        $discount = $saleItem->discount;

        if (!$this->isValidActiveDiscount($discount, $basePrice)) {
            return new PurchasablePriceDTO(
                basePrice: $basePrice,
                finalPrice: $basePrice,
            );
        }

        $finalPrice = (int)$discount->discounted_price;
        $unitDiscountAmount = max(0, $basePrice - $finalPrice);

        return new PurchasablePriceDTO(
            basePrice: $basePrice,
            finalPrice: $finalPrice,
            unitDiscountAmount: $unitDiscountAmount,
            discountSourceType: 'product_sale_item_discount',
            discountSourceId: $discount->id,
            discountTitle: $discount->special_box_title ?: 'تخفیف ویژه محصول',
        );
    }

    private function isValidActiveDiscount(?ProductSaleItemDiscount $discount, int $basePrice): bool
    {
        if (!$discount instanceof ProductSaleItemDiscount) {
            return false;
        }

        if ($basePrice <= 0) {
            return false;
        }

        $discountedPrice = (int)$discount->discounted_price;

        if ($discountedPrice < 0 || $discountedPrice >= $basePrice) {
            return false;
        }

        return $discount->isActiveAt();
    }
}
