<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClaimGuestCartForUserAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction $recalculateCartItemTotalsAction,
        private readonly RecalculateCartTotalsAction     $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(User $user, ?Cart $guestCart): ?Cart
    {
        if (!$guestCart || !$guestCart->isEditable()) {
            return $this->getUserActiveCart($user);
        }

        if (!$guestCart->isGuestCart()) {
            return $guestCart->refresh();
        }

        return DB::transaction(function () use ($user, $guestCart): Cart {
            $guestCart = $guestCart->refresh();

            $userCart = $this->getUserActiveCart($user);

            if (!$userCart || (int)$userCart->id === (int)$guestCart->id) {
                $guestCart->forceFill([
                    'user_id' => $user->id,
                    'session_id' => null,
                ])->save();

                return $this->recalculateCartTotalsAction->execute($guestCart->refresh());
            }

            $this->mergeGuestCartIntoUserCart(
                guestCart: $guestCart,
                userCart: $userCart,
            );

            $guestCart->forceFill([
                'status' => CartStatusEnum::CANCELLED,
                'session_id' => null,
            ])->save();

            return $this->recalculateCartTotalsAction->execute($userCart->refresh());
        });
    }

    private function getUserActiveCart(User $user): ?Cart
    {
        return Cart::query()
            ->where('user_id', $user->id)
            ->where('status', CartStatusEnum::ACTIVE->value)
            ->latest('id')
            ->first();
    }

    private function mergeGuestCartIntoUserCart(Cart $guestCart, Cart $userCart): void
    {
        $guestCart->load(['items.children']);

        $rootItems = $guestCart->items
            ->filter(fn(CartItem $item): bool => !$item->isChild())
            ->values();

        foreach ($rootItems as $guestItem) {
            $existingItem = CartItem::query()
                ->where('cart_id', $userCart->id)
                ->whereNull('parent_cart_item_id')
                ->where('purchasable_type', $guestItem->purchasable_type)
                ->where('purchasable_id', $guestItem->purchasable_id)
                ->first();

            if ($existingItem) {
                $existingItem->forceFill([
                    'quantity' => (int)$existingItem->quantity + (int)$guestItem->quantity,
                ])->save();

                $this->recalculateCartItemTotalsAction->execute($existingItem->refresh());

                foreach ($guestItem->children as $guestChild) {
                    $this->moveChildItemToParent(
                        child: $guestChild,
                        userCart: $userCart,
                        parent: $existingItem,
                    );
                }

                $guestItem->delete();

                continue;
            }

            $guestItem->forceFill([
                'cart_id' => $userCart->id,
                'parent_cart_item_id' => null,
            ])->save();

            foreach ($guestItem->children as $guestChild) {
                $this->moveChildItemToParent(
                    child: $guestChild,
                    userCart: $userCart,
                    parent: $guestItem,
                );
            }

            $this->recalculateCartItemTotalsAction->execute($guestItem->refresh());
        }
    }

    private function moveChildItemToParent(CartItem $child, Cart $userCart, CartItem $parent): void
    {
        $existingChild = CartItem::query()
            ->where('cart_id', $userCart->id)
            ->where('parent_cart_item_id', $parent->id)
            ->where('purchasable_type', $child->purchasable_type)
            ->where('purchasable_id', $child->purchasable_id)
            ->first();

        if ($existingChild) {
            $child->delete();
            return;
        }

        $child->forceFill([
            'cart_id' => $userCart->id,
            'parent_cart_item_id' => $parent->id,
            'quantity' => 1,
        ])->save();

        $this->recalculateCartItemTotalsAction->execute($child->refresh());
    }
}
