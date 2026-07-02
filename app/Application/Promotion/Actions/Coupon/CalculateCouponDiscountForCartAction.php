<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Application\Promotion\DTOs\CouponDiscountResultDTO;
use App\Application\Promotion\Exceptions\CouponMinimumOrderAmountNotMetException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponTarget;
use DomainException;
use Illuminate\Database\Eloquent\Relations\Relation;

class CalculateCouponDiscountForCartAction
{
    public function execute(Cart $cart, Coupon $coupon, ?User $user = null): CouponDiscountResultDTO
    {
        $cart->loadMissing([
            'items.purchasable',
            'items.purchasable.product',
            'items.parent.purchasable',
            'items.parent.purchasable.product',
        ]);

        $coupon->loadMissing([
            'targets',
            'couponUsers',
        ]);

        $this->validateCoupon($cart, $coupon, $user);

        $eligibleAmount = $cart->items
            ->filter(fn(CartItem $item): bool => $this->isEligibleItem($item, $coupon))
            ->sum(fn(CartItem $item): int => max(0, (int)$item->total_amount));

        $eligibleAmount = (int)$eligibleAmount;

        if ($eligibleAmount <= 0) {
            throw new DomainException('Coupon has no eligible cart items.');
        }

        $discountAmount = match ($coupon->type) {
            CouponTypeEnum::PERCENTAGE => (int)floor($eligibleAmount * ((int)$coupon->value) / 100),
            CouponTypeEnum::FIXED_AMOUNT => (int)$coupon->value,
        };

        $discountAmount = min($discountAmount, $eligibleAmount);

        if ($coupon->max_discount_amount !== null) {
            $discountAmount = min($discountAmount, (int)$coupon->max_discount_amount);
        }

        $discountAmount = max(0, $discountAmount);

        if ($discountAmount <= 0) {
            throw new DomainException('Coupon discount amount is zero.');
        }

        return new CouponDiscountResultDTO(
            eligibleAmount: $eligibleAmount,
            discountAmount: $discountAmount,
        );
    }

    private function validateCoupon(Cart $cart, Coupon $coupon, ?User $user): void
    {
        if (!$cart->isEditable()) {
            throw new DomainException('Cart is not editable.');
        }

        if ($cart->items->isEmpty()) {
            throw new DomainException('Cart is empty.');
        }

        if (!$coupon->isCurrentlyUsable()) {
            throw new DomainException('Coupon is not currently usable.');
        }

        if ($coupon->hasReachedUsageLimit()) {
            throw new DomainException('Coupon usage limit reached.');
        }

        $cartAmountAfterProductDiscount = $this->cartAmountAfterProductDiscount($cart);

        if (
            $coupon->minimum_order_amount !== null
            && $cartAmountAfterProductDiscount < (int)$coupon->minimum_order_amount
        ) {
            throw new CouponMinimumOrderAmountNotMetException(
                minimumOrderAmount: (int)$coupon->minimum_order_amount,
                currentAmount: $cartAmountAfterProductDiscount,
            );
        }

        if ($coupon->isLimitedToSpecificUsers()) {
            if (!$user) {
                throw new DomainException('Coupon requires authenticated user.');
            }

            $isAllowedUser = $coupon->couponUsers
                ->contains(fn($couponUser): bool => (int)$couponUser->user_id === (int)$user->id);

            if (!$isAllowedUser) {
                throw new DomainException('Coupon is not allowed for this user.');
            }
        }

        if ($coupon->usage_limit_per_user !== null) {
            if (!$user) {
                throw new DomainException('Coupon requires authenticated user.');
            }

            $usedByUser = $coupon->redemptions()
                ->where('user_id', $user->id)
                ->count();

            if ($usedByUser >= (int)$coupon->usage_limit_per_user) {
                throw new DomainException('Coupon per user usage limit reached.');
            }
        }
    }

    private function cartAmountAfterProductDiscount(Cart $cart): int
    {
        return (int)$cart->items
            ->sum(fn(CartItem $item): int => max(0, (int)$item->total_amount));
    }

    private function isEligibleItem(CartItem $item, Coupon $coupon): bool
    {
        if ($item->isChild() && !$coupon->applies_to_addons) {
            return false;
        }

        return match ($coupon->scope) {
            CouponScopeEnum::ORDER => true,
            CouponScopeEnum::PRODUCTS => $this->isEligibleProductItem($item, $coupon),
            CouponScopeEnum::STORE => $this->isEligibleStoreItem($item, $coupon),
        };
    }

    private function isEligibleProductItem(CartItem $item, Coupon $coupon): bool
    {
        $productId = $this->productIdForItem($item);

        if (!$productId) {
            return false;
        }

        return in_array($productId, $this->targetIds($coupon, Product::class), true);
    }

    private function isEligibleStoreItem(CartItem $item, Coupon $coupon): bool
    {
        $storeId = $this->storeIdForItem($item);

        if (!$storeId) {
            return false;
        }

        return in_array($storeId, $this->targetIds($coupon, Store::class), true);
    }

    /**
     * @return list<int>
     */
    private function targetIds(Coupon $coupon, string $targetClass): array
    {
        $targetTypes = array_unique([
            $targetClass,
            Relation::getMorphAlias($targetClass),
        ]);

        return $coupon->targets
            ->filter(fn(CouponTarget $target): bool => in_array($target->target_type, $targetTypes, true))
            ->pluck('target_id')
            ->map(fn($id): int => (int)$id)
            ->values()
            ->all();
    }

    private function productIdForItem(CartItem $item): ?int
    {
        $productId = data_get($item, 'snapshot.product_id')
            ?? data_get($item, 'metadata.product_id')
            ?? data_get($item, 'purchasable.product_id')
            ?? data_get($item, 'purchasable.product.id')
            ?? data_get($item, 'parent.snapshot.product_id')
            ?? data_get($item, 'parent.metadata.product_id')
            ?? data_get($item, 'parent.purchasable.product_id')
            ?? data_get($item, 'parent.purchasable.product.id');

        return $productId ? (int)$productId : null;
    }

    private function storeIdForItem(CartItem $item): ?int
    {
        $storeId = data_get($item, 'snapshot.store_id')
            ?? data_get($item, 'metadata.store_id')
            ?? data_get($item, 'purchasable.store_id')
            ?? data_get($item, 'purchasable.product.store_id')
            ?? data_get($item, 'parent.snapshot.store_id')
            ?? data_get($item, 'parent.metadata.store_id')
            ?? data_get($item, 'parent.purchasable.store_id')
            ?? data_get($item, 'parent.purchasable.product.store_id');

        return $storeId ? (int)$storeId : null;
    }
}
