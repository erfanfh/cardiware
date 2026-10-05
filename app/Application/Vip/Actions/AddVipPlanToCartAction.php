<?php

namespace App\Application\Vip\Actions;

use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Cart\GetOrCreateActiveCartAction;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use DomainException;
use Illuminate\Support\Facades\DB;

class AddVipPlanToCartAction
{
    public function execute(VipPlan $plan, User $user): CartItem
    {
        return DB::transaction(function () use ($plan, $user): CartItem {
            if (! $plan->isPurchasableAvailable()) {
                throw new DomainException('VIP plan is not available.');
            }

            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $cart = app(GetOrCreateActiveCartAction::class)->execute();

            $existing = $cart->items()
                ->whereNull('parent_cart_item_id')
                ->where('purchasable_type', $plan->getMorphClass())
                ->where('purchasable_id', $plan->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            return app(AddPurchasableToCartAction::class)->execute($plan);
        }, 3);
    }
}
