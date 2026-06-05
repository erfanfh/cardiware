<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class AttachGuestCartToUserAction
{
    public function __construct(
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Attach current guest cart to authenticated user.
     * @throws Throwable
     */
    public function execute(int $userId, string $sessionId): ?Cart
    {
        return DB::transaction(function () use ($userId, $sessionId) {
            $guestCart = Cart::query()
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->where('status', CartStatusEnum::ACTIVE)
                ->latest('id')
                ->first();

            if (!$guestCart instanceof Cart) {
                return null;
            }

            $userCart = Cart::query()
                ->where('user_id', $userId)
                ->where('status', CartStatusEnum::ACTIVE)
                ->latest('id')
                ->first();

            if (!$userCart instanceof Cart) {
                $guestCart->forceFill([
                    'user_id' => $userId,
                    'expires_at' => null,
                ])->save();

                return $guestCart->refresh();
            }

            foreach ($guestCart->items()->whereNull('parent_cart_item_id')->get() as $rootItem) {
                $rootItem->forceFill([
                    'cart_id' => $userCart->id,
                ])->save();

                $rootItem->children()->update([
                    'cart_id' => $userCart->id,
                ]);
            }

            $guestCart->delete();

            return $this->recalculateCartTotalsAction->execute($userCart);
        });
    }
}
