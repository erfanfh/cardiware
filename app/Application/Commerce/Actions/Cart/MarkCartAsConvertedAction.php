<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use DomainException;

class MarkCartAsConvertedAction
{
    /**
     * Mark the cart as converted to an order.
     */
    public function execute(Cart $cart): Cart
    {
        if ($cart->status !== CartStatusEnum::CHECKOUT_STARTED) {
            throw new DomainException('Only checkout started carts can be converted.');
        }

        $cart->forceFill([
            'status' => CartStatusEnum::CONVERTED,
        ])->save();

        return $cart->refresh();
    }
}
