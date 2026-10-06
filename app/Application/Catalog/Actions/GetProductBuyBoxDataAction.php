<?php

namespace App\Application\Catalog\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Application\Loyalty\Earning\Actions\ResolveActiveEarningRuleAction;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

final class GetProductBuyBoxDataAction
{
    public function __construct(
        private readonly ResolveActiveEarningRuleAction $resolveActiveEarningRuleAction,
    ) {
    }

    /**
     * @return array{
     *     saleItems: array<int, array<string, mixed>>,
     *     addons: array<int, array<string, mixed>>,
     *     hasAvailableSaleItems: bool
     * }
     */
    public function execute(Product $product): array
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_DETAIL,
            CacheTag::PRODUCT_BUY_BOX,
            CacheTag::product($product->getKey()),
        ])->remember(
            CacheKey::productBuyBoxData(
                productId: $product->getKey(),
                productUpdatedAt: $product->updated_at?->timestamp ?? 0,
            ).':vip-claim-v3',
            $this->smartExpiresAt($product),
            fn() => $this->resolve($product)
        );
    }

    private function smartExpiresAt(Product $product): DateTimeInterface
    {
        $now = CarbonImmutable::now();

        $nextDiscountBoundary = $this->nextDiscountBoundary($product, $now);

        if (!$nextDiscountBoundary) {
            return $now->addHours(6);
        }

        $expiresAt = $nextDiscountBoundary->addSecond();

        if ($expiresAt->lessThanOrEqualTo($now)) {
            return $now->addSeconds(30);
        }

        return $expiresAt;
    }

    private function nextDiscountBoundary(Product $product, CarbonImmutable $now): ?CarbonImmutable
    {
        $discounts = ProductSaleItemDiscount::query()
            ->whereHas('saleItem', function ($query) use ($product) {
                $query
                    ->where('product_id', $product->getKey())
                    ->where('status', ProductSaleItemStatusEnum::AVAILABLE);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->where('starts_at', '>', $now)
                    ->orWhere('ends_at', '>', $now);
            })
            ->get(['starts_at', 'ends_at']);

        return $discounts
            ->flatMap(function (ProductSaleItemDiscount $discount) use ($now) {
                return collect([
                    $discount->starts_at,
                    $discount->ends_at,
                ])->filter(fn($date) => $date && $date->greaterThan($now));
            })
            ->map(fn($date) => CarbonImmutable::instance($date))
            ->sort()
            ->first();
    }

    /**
     * @return array{
     *     saleItems: array<int, array<string, mixed>>,
     *     addons: array<int, array<string, mixed>>,
     *     hasAvailableSaleItems: bool
     * }
     */
    private function resolve(Product $product): array
    {
        $saleItems = $this->saleItems($product);
        $addons = $this->addons($product);

        return [
            'saleItems' => $saleItems,
            'addons' => $addons,
            'purchasePoints' => $this->purchasePoints($product, $saleItems),
            'hasAvailableSaleItems' => count($saleItems) > 0,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function saleItems(Product $product): array
    {
        return $product
            ->saleItems()
            ->where('status', ProductSaleItemStatusEnum::AVAILABLE)
            ->with([
                'discount',

                'attributeValues.globalValue.attribute',
                'attributeValues.customValue.customAttribute',
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn(ProductSaleItem $saleItem) => $this->makeSaleItemData($saleItem))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function makeSaleItemData(ProductSaleItem $saleItem): array
    {
        $pricing = $this->resolveSaleItemPricing($saleItem);

        return [
            'id' => $saleItem->id,
            'title' => $this->makeSaleItemTitle($saleItem),
            'description' => $saleItem->description,
            'vip_access_enabled' => (bool) ($saleItem->vip_access_enabled
                && ! $saleItem->launcher_enabled
                && $saleItem->files()->where('is_active', true)
                    ->whereNotNull('url')->where('url', '<>', '')->exists()),

            // قیمت قابل پرداخت؛ این مقدار باید برای totalPrice استفاده شود.
            'price' => $pricing['final_price'],

            // اطلاعات نمایشی تخفیف
            'regular_price' => $pricing['regular_price'],
            'has_discount' => $pricing['has_discount'],
            'discount_amount' => $pricing['discount_amount'],
            'discount_percent' => $pricing['discount_percent'],

            'special_box' => $pricing['special_box'],

            'status' => $saleItem->status instanceof \BackedEnum
                ? $saleItem->status->value
                : $saleItem->status,

            'max_quantity_per_order' => $saleItem->max_quantity_per_order,
        ];
    }

    /**
     * @return array{
     *     regular_price: int,
     *     final_price: int,
     *     has_discount: bool,
     *     discount_amount: int,
     *     discount_percent: int,
     *     special_box: array{
     *         show: bool,
     *         title: string|null,
     *         description: string|null
     *     }
     * }
     */
    private function resolveSaleItemPricing(ProductSaleItem $saleItem): array
    {
        $regularPrice = max(0, (int)$saleItem->base_price);

        $discount = $saleItem->discount;

        $hasDiscount = $this->isValidActiveDiscount(
            discount: $discount,
            regularPrice: $regularPrice,
        );

        $finalPrice = $hasDiscount
            ? (int)$discount->discounted_price
            : $regularPrice;

        $discountAmount = max(0, $regularPrice - $finalPrice);

        $discountPercent = $regularPrice > 0 && $discountAmount > 0
            ? (int)round(($discountAmount / $regularPrice) * 100)
            : 0;

        return [
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice,
            'has_discount' => $hasDiscount,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,

            'special_box' => [
                'show' => $hasDiscount && (bool)$discount?->show_special_box,
                'title' => $discount?->special_box_title,
                'description' => $discount?->special_box_description,

                'starts_at_ts' => $hasDiscount && $discount?->starts_at
                    ? $discount->starts_at->timestamp * 1000
                    : null,

                'ends_at_ts' => $hasDiscount && $discount?->ends_at
                    ? $discount->ends_at->timestamp * 1000
                    : null,

                'ends_at_label' => $hasDiscount && $discount?->ends_at
                    ? jdate($discount->ends_at)->format('H:i Y/m/d')
                    : null,
            ],
        ];
    }

    private function isValidActiveDiscount(
        ?ProductSaleItemDiscount $discount,
        int                      $regularPrice,
    ): bool
    {
        if (!$discount instanceof ProductSaleItemDiscount) {
            return false;
        }

        if ($regularPrice <= 0) {
            return false;
        }

        $discountedPrice = (int)$discount->discounted_price;

        if ($discountedPrice < 0 || $discountedPrice >= $regularPrice) {
            return false;
        }

        return $discount->isActiveAt();
    }

    /**
     * @param array<int, array<string, mixed>> $saleItems
     * @return array{enabled: bool, amountStep: int|null, pointsPerStep: int|null, items: array<int, int>}
     */
    private function purchasePoints(Product $product, array $saleItems): array
    {
        $rule = $this->resolveActiveEarningRuleAction->execute(
            EarningRuleTypeEnum::PURCHASE,
            now(),
        );

        if (!$rule) {
            return [
                'enabled' => false,
                'amountStep' => null,
                'pointsPerStep' => null,
                'items' => [],
            ];
        }

        $amountStep = (int)($rule->config['amount_step'] ?? 0);
        $pointsPerStep = (int)($rule->config['points'] ?? 0);
        $minimumAmount = (int)($rule->config['minimum_order_amount'] ?? 0);
        $maxPoints = $rule->config['max_points_per_order'] ?? null;

        $items = [];
        foreach ($saleItems as $item) {
            $amount = (int)($item['price'] ?? 0);
            if ($amount < $minimumAmount || $amountStep <= 0 || $pointsPerStep <= 0) {
                $items[$item['id']] = 0;
                continue;
            }

            $points = intdiv($amount, $amountStep) * $pointsPerStep;
            if ($maxPoints !== null) {
                $points = min($points, (int)$maxPoints);
            }

            $items[$item['id']] = $points;
        }

        return [
            'enabled' => true,
            'amountStep' => $amountStep,
            'pointsPerStep' => $pointsPerStep,
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function addons(Product $product): array
    {
        return $product
            ->addons()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($addon) => [
                'id' => $addon->id,
                'title' => $addon->title,
                'description' => $addon->description,
                'price' => (int)$addon->price,
                'max_quantity_per_order' => (int)$addon->max_quantity_per_order,
            ])
            ->values()
            ->all();
    }

    private function makeSaleItemTitle(ProductSaleItem $saleItem): string
    {
        $labels = $saleItem->attributeValues
            ->map(function ($selectedValue) {
                if ($selectedValue->globalValue) {
                    return $selectedValue->globalValue->attribute?->title . ': ' . $selectedValue->globalValue->title;
                }

                if ($selectedValue->customValue) {
                    return $selectedValue->customValue->customAttribute?->title . ': ' . $selectedValue->customValue->title;
                }

                return null;
            })
            ->filter()
            ->values();

        if ($labels->isNotEmpty()) {
            return $labels->implode(' / ');
        }

        return 'نسخه اصلی محصول';
    }
}
