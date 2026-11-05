<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Cart\GetOrCreateActiveCartAction;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherMod;
use DomainException;
use Illuminate\Support\Facades\DB;

class AddLauncherModToCartAction
{
    public function execute(LauncherMod $mod, User $user): CartItem
    {
        return DB::transaction(function () use ($mod, $user): CartItem {
            if (! $mod->isPurchasableAvailable()) {
                throw new DomainException('این ماد در حال حاضر قابل خرید نیست.');
            }

            $existingGrant = $mod->grants()
                ->where('user_id', $user->getKey())
                ->exists();

            if ($existingGrant) {
                throw new DomainException('این ماد قبلاً برای حساب شما فعال شده است.');
            }

            if (! self::userHasProductAccess($mod, $user)) {
                throw new DomainException('برای خرید این ماد ابتدا باید خودِ محصول را داشته باشید.');
            }

            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $cart = app(GetOrCreateActiveCartAction::class)->execute();

            $existing = $cart->items()
                ->whereNull('parent_cart_item_id')
                ->where('purchasable_type', $mod->getMorphClass())
                ->where('purchasable_id', $mod->getKey())
                ->first();

            if ($existing) {
                return $existing;
            }

            return app(AddPurchasableToCartAction::class)->execute($mod);
        }, 3);
    }

    /** ماد فقط برای کسی فروخته می‌شود که محصولِ مادر را در لانچر دارد (خرید یا VIP فعال). */
    public static function userHasProductAccess(LauncherMod $mod, User $user): bool
    {
        return PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->where('product_sale_item_id', $mod->product_sale_item_id)
            ->get()
            ->contains(static fn(PurchaseAccess $access): bool => $access->setRelation('user', $user)->isUsableForLauncher());
    }
}
