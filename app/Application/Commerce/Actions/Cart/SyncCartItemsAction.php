<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SyncCartItemsAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction $recalculateCartItemTotalsAction,
        private readonly RecalculateCartTotalsAction     $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Sync cart items with current purchasable data.
     *
     * @return array<string, mixed>
     */
    public function execute(Cart $cart): array
    {
        return DB::transaction(function () use ($cart) {
            $cart = $cart->refresh();

            if (!$cart->isEditable()) {
                return [
                    'cart' => $cart,
                    'changed_items' => [],
                    'has_changes' => false,
                    'skipped' => true,
                ];
            }

            $cart->loadMissing([
                'items.purchasable',
            ]);

            $changedItems = [];

            foreach ($cart->items as $cartItem) {
                $purchasable = $cartItem->purchasable;

                if (!$purchasable instanceof Model || !$purchasable instanceof Purchasable) {
                    continue;
                }

                $changes = $this->syncCartItem($cartItem, $purchasable);

                if ($changes !== []) {
                    $changedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'changes' => $changes,
                    ];
                }
            }

            $cart = $this->recalculateCartTotalsAction->execute($cart);

            $cart->forceFill([
                'synced_at' => now(),
            ])->save();

            return [
                'cart' => $cart->refresh(),
                'changed_items' => $changedItems,
                'has_changes' => count($changedItems) > 0,
                'skipped' => false,
            ];
        });
    }

    /**
     * Sync a single cart item.
     *
     * @param Purchasable&Model $purchasable
     *
     * @return array<string, mixed>
     */
    private function syncCartItem(CartItem $cartItem, Purchasable&Model $purchasable): array
    {
        $changes = [];

        $currentPrice = $purchasable->getPurchasablePrice();
        $currentSnapshot = $purchasable->getPurchasableSnapshot();

        if ((int)$cartItem->unit_price !== (int)$currentPrice) {
            $changes['unit_price'] = [
                'old' => $cartItem->unit_price,
                'new' => $currentPrice,
            ];

            $cartItem->unit_price = $currentPrice;
        }

        if (($cartItem->snapshot ?? []) !== $currentSnapshot) {
            $changes['snapshot'] = true;

            $cartItem->snapshot = $currentSnapshot;
        }

        if ($changes !== []) {
            $cartItem->save();

            $this->recalculateCartItemTotalsAction->execute($cartItem);
        }

        return $changes;
    }
}
