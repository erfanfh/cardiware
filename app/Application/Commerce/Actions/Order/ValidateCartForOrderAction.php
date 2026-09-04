<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Support\TaxCalculator;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use DomainException;

class ValidateCartForOrderAction
{
    public function __construct(
        private readonly TaxCalculator $taxCalculator,
    ) {
    }

    /**
     * Validate cart before creating an order.
     */
    public function execute(Cart $cart): void
    {
        if ($cart->status !== CartStatusEnum::CHECKOUT_STARTED) {
            throw new DomainException('Only checkout started carts can be converted to order.');
        }

        if ($cart->items()->doesntExist()) {
            throw new DomainException('Empty carts cannot be converted to order.');
        }

        if ($cart->subtotal_amount < 0 || $cart->discount_amount < 0 || $cart->taxable_amount < 0
            || $cart->tax_amount < 0 || $cart->total_amount < 0) {
            throw new DomainException('Cart amounts are invalid.');
        }

        if ($cart->discount_amount > $cart->subtotal_amount) {
            throw new DomainException('Cart discount amount is invalid.');
        }

        if ($cart->tax_rate_basis_points < 0 || $cart->tax_rate_basis_points > 10_000) {
            throw new DomainException('Cart tax rate is invalid.');
        }

        $taxableAmount = max(0, $cart->subtotal_amount - $cart->discount_amount);

        if ((int)$cart->taxable_amount !== $taxableAmount) {
            throw new DomainException('Cart taxable amount is invalid.');
        }

        $tax = $this->taxCalculator->calculateAtRate(
            $taxableAmount,
            (int)$cart->tax_rate_basis_points,
        );

        if ((int)$cart->tax_amount !== $tax->taxAmount
            || (int)$cart->total_amount !== $tax->totalAmount) {
            throw new DomainException('Cart total amount is invalid.');
        }

        $this->validateChildItemsHaveParent($cart);
    }

    /**
     * Validate child cart items have valid parent items in the same cart.
     */
    private function validateChildItemsHaveParent(Cart $cart): void
    {
        $invalidChildExists = $cart->items()
            ->whereNotNull('parent_cart_item_id')
            ->whereDoesntHave('parent', function ($query) use ($cart) {
                $query->where('cart_id', $cart->id);
            })
            ->exists();

        if ($invalidChildExists) {
            throw new DomainException('Cart contains invalid dependent items.');
        }
    }
}
