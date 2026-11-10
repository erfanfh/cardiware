<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;

/**
 * ابطال دسترسی «یک محصول» از پنل مدیریت دستگاه‌های لانچر — مکمل مرجوعی:
 * وقتی کاربر چند محصول خریده و فقط مبلغ یکی عودت داده می‌شود، همان محصول از
 * کتابخانه و لانچر حذف می‌شود و بقیه خریدها دست‌نخورده می‌مانند (برخلاف
 * محرومیت حساب که همه‌چیز را می‌بندد).
 */
class AdminRevokePurchaseAccessAction
{
    public function __construct(
        private readonly UserCacheInvalidator $userCacheInvalidator,
    ) {}

    public function execute(PurchaseAccess $access, User $admin, string $reason): PurchaseAccess
    {
        $access->forceFill([
            'status' => PurchaseAccessStatusEnum::REVOKED,
            'metadata' => array_merge($access->metadata ?? [], [
                'revoked_at' => now()->toDateTimeString(),
                'revoked_reason' => $reason,
                'revoked_by_admin_id' => $admin->getKey(),
            ]),
        ])->save();

        $this->userCacheInvalidator->flushForUser((int) $access->user_id);

        return $access->refresh();
    }
}
