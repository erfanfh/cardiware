<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * @throws Throwable
     */
    public function execute(Cart $cart): array
    {
        return DB::transaction(function () use ($cart): array {
            $cart = $cart->refresh();

            if (!$cart->isEditable()) {
                return [
                    'cart' => $cart,
                    'changed_items' => [],
                    'removed_items' => [],
                    'updated_items_count' => 0,
                    'removed_items_count' => 0,
                    'has_changes' => false,
                    'skipped' => true,
                ];
            }

            $cart->load([
                'items.purchasable',
                'items.purchasable.product',
                'items.children.purchasable',
                'items.children.purchasable.product',
            ]);

            $changedItems = [];
            $removedItems = [];

            /**
             * اول parent ها را sync می‌کنیم.
             * اگر parent حذف شود، child ها هم همراهش حذف می‌شوند.
             */
            $mainItems = $cart->items
                ->filter(fn(CartItem $item): bool => !$item->isChild())
                ->values();

            foreach ($mainItems as $cartItem) {
                $result = $this->syncCartItem($cartItem);

                if ($result['removed']) {
                    $removedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'title' => $this->cartItemTitle($cartItem),
                        'reason' => $result['reason'],
                    ];

                    continue;
                }

                if ($result['changes'] !== []) {
                    $changedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'title' => $this->cartItemTitle($cartItem),
                        'changes' => $result['changes'],
                    ];
                }
            }

            /**
             * حالا child ها را جدا sync می‌کنیم.
             * چون ممکنه parent هنوز باقی مانده باشد ولی خود addon غیرفعال شده باشد.
             */
            $cart = $cart->refresh();

            $cart->load([
                'items.purchasable',
                'items.purchasable.product',
                'items.parent',
            ]);

            $childItems = $cart->items
                ->filter(fn(CartItem $item): bool => $item->isChild())
                ->values();

            foreach ($childItems as $cartItem) {
                /**
                 * اگر parent وجود ندارد، child باید حذف شود.
                 */
                if (!$cartItem->parent) {
                    $removedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'title' => $this->cartItemTitle($cartItem),
                        'reason' => 'parent_missing',
                    ];

                    $cartItem->delete();

                    continue;
                }

                $result = $this->syncCartItem($cartItem);

                if ($result['removed']) {
                    $removedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'title' => $this->cartItemTitle($cartItem),
                        'reason' => $result['reason'],
                    ];

                    continue;
                }

                if ($result['changes'] !== []) {
                    $changedItems[] = [
                        'cart_item_id' => $cartItem->id,
                        'title' => $this->cartItemTitle($cartItem),
                        'changes' => $result['changes'],
                    ];
                }
            }

            $cart = $this->recalculateCartTotalsAction->execute($cart->refresh());

            $cart->forceFill([
                'synced_at' => now(),
            ])->save();

            return [
                'cart' => $cart->refresh(),
                'changed_items' => $changedItems,
                'removed_items' => $removedItems,
                'updated_items_count' => count($changedItems),
                'removed_items_count' => count($removedItems),
                'has_changes' => count($changedItems) > 0 || count($removedItems) > 0,
                'skipped' => false,
            ];
        });
    }

    /**
     * Sync a single cart item.
     *
     * @return array{
     *     removed: bool,
     *     reason: string|null,
     *     changes: array<string, mixed>
     * }
     */
    private function syncCartItem(CartItem $cartItem): array
    {
        $cartItem->loadMissing('purchasable');

        $purchasable = $cartItem->purchasable;

        if (!$purchasable instanceof Model || !$purchasable instanceof Purchasable) {
            $this->deleteCartItemWithChildren($cartItem);

            return [
                'removed' => true,
                'reason' => 'invalid_purchasable',
                'changes' => [],
            ];
        }

        if (!$purchasable->isPurchasableAvailable()) {
            $this->deleteCartItemWithChildren($cartItem);

            return [
                'removed' => true,
                'reason' => 'not_available',
                'changes' => [],
            ];
        }

        $changes = [];

        $currentPrice = (int)$purchasable->getPurchasablePrice();
        $currentSnapshot = $purchasable->getPurchasableSnapshot();
        $maxQuantityPerOrder = $purchasable->getMaxQuantityPerOrder();

        if ((int)$cartItem->unit_price !== $currentPrice) {
            $changes['unit_price'] = [
                'old' => (int)$cartItem->unit_price,
                'new' => $currentPrice,
            ];

            $cartItem->unit_price = $currentPrice;
        }

        $oldSnapshotForComparison = $this->normalizeSnapshotForComparison($cartItem->snapshot ?? []);
        $currentSnapshotForComparison = $this->normalizeSnapshotForComparison($currentSnapshot);

        if ($oldSnapshotForComparison !== $currentSnapshotForComparison) {
            $changes['snapshot'] = true;

            $cartItem->snapshot = $currentSnapshot;
        }

        if ($maxQuantityPerOrder !== null && (int)$cartItem->quantity > (int)$maxQuantityPerOrder) {
            $changes['quantity'] = [
                'old' => (int)$cartItem->quantity,
                'new' => (int)$maxQuantityPerOrder,
            ];

            $cartItem->quantity = (int)$maxQuantityPerOrder;
        }

        /**
         * برای child/addon بهتر است quantity همیشه 1 بماند.
         */
        if ($cartItem->isChild() && (int)$cartItem->quantity !== 1) {
            $changes['quantity'] = [
                'old' => (int)$cartItem->quantity,
                'new' => 1,
            ];

            $cartItem->quantity = 1;
        }

        if ($changes !== []) {
            $cartItem->save();

            $this->recalculateCartItemTotalsAction->execute($cartItem->refresh());
        }

        return [
            'removed' => false,
            'reason' => null,
            'changes' => $changes,
        ];
    }

    private function normalizeSnapshotForComparison(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeSnapshotForComparison($item);
        }

        if (!array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    private function deleteCartItemWithChildren(CartItem $cartItem): void
    {
        $cartItem->loadMissing('children');

        foreach ($cartItem->children as $child) {
            $child->delete();
        }

        $cartItem->delete();
    }

    private function cartItemTitle(CartItem $cartItem): string
    {
        return data_get($cartItem->snapshot, 'title')
            ?? data_get($cartItem->snapshot, 'product_title')
            ?? 'آیتم سبد خرید';
    }
}
