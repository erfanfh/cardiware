<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class RecalculateCartItemTotalsAction
{
    public function __construct(
        private readonly ResolvePurchasablePriceAction $resolvePurchasablePriceAction,
    )
    {
    }

    public function execute(CartItem $cartItem): CartItem
    {
        $cartItem->loadMissing('purchasable');

        $purchasable = $cartItem->purchasable;

        if (!$purchasable instanceof Model || !$purchasable instanceof Purchasable) {
            return $cartItem;
        }

        $quantity = max(1, (int)$cartItem->quantity);

        if ($cartItem->isChild()) {
            $quantity = 1;
        }

        $price = $this->resolvePurchasablePriceAction->execute($purchasable);

        $snapshot = $purchasable->getPurchasableSnapshot();

        $cartItem->forceFill([
            'quantity' => $quantity,
            'unit_price' => $price->finalPrice,
            'subtotal_amount' => $price->subtotalFor($quantity),
            'discount_amount' => $price->discountFor($quantity),
            'coupon_discount_amount' => 0,
            'total_amount' => $price->totalFor($quantity),
            'final_total_amount' => $price->totalFor($quantity),
            'snapshot' => $snapshot,
        ])->save();

        return $cartItem->refresh();
    }
}
