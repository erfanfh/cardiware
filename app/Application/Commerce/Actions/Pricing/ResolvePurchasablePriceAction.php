<?php

namespace App\Application\Commerce\Actions\Pricing;

use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\DTOs\PurchasablePriceDTO;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Illuminate\Database\Eloquent\Model;

class ResolvePurchasablePriceAction
{
    public function __construct(
        private readonly VipSettings $vipSettings,
    )
    {
    }

    /**
     * @param Purchasable&Model $purchasable
     */
    public function execute(Purchasable&Model $purchasable, ?User $buyer = null): PurchasablePriceDTO
    {
        if ($purchasable instanceof ProductSaleItem) {
            return $this->resolveProductSaleItemPrice($purchasable, $buyer);
        }

        // پلن‌های اشتراک VIP و سایر purchasableها مشمول تخفیف اعضای VIP نیستند.
        $price = (int)$purchasable->getPurchasablePrice();

        return new PurchasablePriceDTO(
            basePrice: $price,
            finalPrice: $price,
        );
    }

    private function resolveProductSaleItemPrice(ProductSaleItem $saleItem, ?User $buyer): PurchasablePriceDTO
    {
        $basePrice = max(0, (int)$saleItem->base_price);

        $saleItem->loadMissing('discount');

        $discount = $saleItem->discount;

        if (!$this->isValidActiveDiscount($discount, $basePrice)) {
            return $this->withVipDiscount(
                basePrice: $basePrice,
                finalPrice: $basePrice,
                unitDiscountAmount: 0,
                discountSourceType: null,
                discountSourceId: null,
                discountTitle: null,
                saleItem: $saleItem,
                buyer: $buyer,
            );
        }

        $finalPrice = (int)$discount->discounted_price;
        $unitDiscountAmount = max(0, $basePrice - $finalPrice);

        return $this->withVipDiscount(
            basePrice: $basePrice,
            finalPrice: $finalPrice,
            unitDiscountAmount: $unitDiscountAmount,
            discountSourceType: 'product_sale_item_discount',
            discountSourceId: $discount->id,
            discountTitle: $discount->special_box_title ?: 'تخفیف ویژه محصول',
            saleItem: $saleItem,
            buyer: $buyer,
        );
    }

    /**
     * تخفیف عمومی اعضای فعال VIP روی قیمت نهایی (بعد از تخفیف محصول، به‌صورت ضربی).
     * اقلام رایگان و اقلامی که با خودِ اشتراک باز می‌شوند مشمول نیستند.
     */
    private function withVipDiscount(
        int $basePrice,
        int $finalPrice,
        int $unitDiscountAmount,
        ?string $discountSourceType,
        ?int $discountSourceId,
        ?string $discountTitle,
        ProductSaleItem $saleItem,
        ?User $buyer,
    ): PurchasablePriceDTO
    {
        // درصد از پلنِ اشتراکِ فعال خریدار؛ اگر پلن درصدی نداشت، درصد سراسری تنظیمات VIP.
        [$percent, $sourceLabel] = $this->vipDiscountPercentFor($buyer);

        $applies =
            $percent > 0
            && $buyer instanceof User
            && $finalPrice > 0
            && !$saleItem->vip_access_enabled;

        if (!$applies) {
            return new PurchasablePriceDTO(
                basePrice: $basePrice,
                finalPrice: $finalPrice,
                unitDiscountAmount: $unitDiscountAmount,
                discountSourceType: $discountSourceType,
                discountSourceId: $discountSourceId,
                discountTitle: $discountTitle,
            );
        }

        $vipFinalPrice = (int)round($finalPrice * (100 - $percent) / 100);
        $vipFinalPrice = max(0, min($vipFinalPrice, $finalPrice - 1)); // حداقل ۱ تومان کاهش واقعی

        $totalUnitDiscount = max(0, $basePrice - $vipFinalPrice);

        return new PurchasablePriceDTO(
            basePrice: $basePrice,
            finalPrice: $vipFinalPrice,
            unitDiscountAmount: $totalUnitDiscount,
            discountSourceType: 'vip_purchase_discount',
            discountSourceId: null,
            discountTitle: 'تخفیف ' . $percent . '٪ ' . $sourceLabel,
        );
    }

    /**
     * @return array{0: int, 1: string} [درصد, برچسب منبع]
     */
    private function vipDiscountPercentFor(?User $buyer): array
    {
        if ($buyer === null) {
            return [0, 'اعضای VIP'];
        }

        $subscription = $buyer->vipSubscriptions()
            ->with('plan')
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now())
            ->latest('starts_at')
            ->first();

        $planPercent = $subscription?->plan?->purchase_discount_percent;

        if ($subscription === null) {
            return [0, 'اعضای VIP'];
        }

        if ($planPercent !== null) {
            return [max(0, min(90, $planPercent)), 'اشتراک ' . $subscription->plan->title];
        }

        $globalPercent = $this->vipSettings->purchaseDiscountPercent();

        return [$globalPercent, 'اعضای VIP'];
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
