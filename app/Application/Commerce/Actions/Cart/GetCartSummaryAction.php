<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;

class GetCartSummaryAction
{
    /**
     * Get cart summary for UI/API.
     *
     * @return array<string, mixed>
     */
    public function execute(Cart $cart): array
    {
        $cart->loadMissing([
            'items.children',
            'items.purchasable',
        ]);

        $rootItems = $cart->items
            ->whereNull('parent_cart_item_id')
            ->values();

        return [
            'id' => $cart->id,
            'status' => $cart->status->value,
            'status_label' => $cart->status->label(),
            'is_editable' => $cart->isEditable(),
            'items_count' => $cart->items->count(),
            'subtotal_amount' => $cart->subtotal_amount,
            'discount_amount' => $cart->discount_amount,
            'taxable_amount' => $cart->taxable_amount,
            'tax_rate_basis_points' => $cart->tax_rate_basis_points,
            'tax_rate_percent' => $cart->tax_rate_basis_points / 100,
            'tax_amount' => $cart->tax_amount,
            'total_amount' => $cart->total_amount,
            'items' => $rootItems
                ->map(fn($item) => $this->mapCartItem($item))
                ->values()
                ->all(),
        ];
    }

    /**
     * Map cart item for summary.
     *
     * @return array<string, mixed>
     */
    private function mapCartItem($cartItem): array
    {
        return [
            'id' => $cartItem->id,
            'parent_cart_item_id' => $cartItem->parent_cart_item_id,
            'purchasable_type' => $cartItem->purchasable_type,
            'purchasable_id' => $cartItem->purchasable_id,
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->unit_price,
            'subtotal_amount' => $cartItem->subtotal_amount,
            'discount_amount' => $cartItem->discount_amount,
            'total_amount' => $cartItem->total_amount,
            'snapshot' => $cartItem->snapshot ?? [],
            'metadata' => $cartItem->metadata ?? [],
            'children' => $cartItem->children
                ->map(fn($child) => $this->mapCartItem($child))
                ->values()
                ->all(),
        ];
    }
}
