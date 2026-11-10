<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * بازگردانی دسترسی یک محصولِ ابطال‌شده از پنل مدیریت دستگاه‌های لانچر.
 * سفارشِ گره‌خورده باید همچنان در وضعیت پرداخت‌شده باشد؛ سفارش مسترد/ابطال‌شده
 * دیگر دسترسی نمی‌دهد تا مسیر دورزدن مرجوعی بسته بماند.
 */
class AdminRestorePurchaseAccessAction
{
    public function __construct(
        private readonly UserCacheInvalidator $userCacheInvalidator,
    ) {}

    public function execute(PurchaseAccess $access, User $admin, string $reason): PurchaseAccess
    {
        $restored = DB::transaction(function () use ($access, $admin, $reason): PurchaseAccess {
            // Match the order -> access lock order used by refund/status transitions.
            $order = $access->order()->lockForUpdate()->first();
            $access = PurchaseAccess::query()->withoutGlobalScopes()
                ->whereKey($access->getKey())->lockForUpdate()->firstOrFail();

            if (($access->order_id !== null && $order === null)
                || ($order !== null && ! $order->status->grantsPurchaseAccess())) {
                throw (new ModelNotFoundException)->setModel(PurchaseAccess::class, [$access->getKey()]);
            }

            $access->forceFill([
                'status' => PurchaseAccessStatusEnum::ACTIVE,
                'metadata' => array_merge($access->metadata ?? [], [
                    'restored_at' => now()->toDateTimeString(),
                    'restored_reason' => $reason,
                    'restored_by_admin_id' => $admin->getKey(),
                ]),
            ])->save();

            return $access->refresh();
        });

        $this->userCacheInvalidator->flushForUser((int) $restored->user_id);

        return $restored;
    }
}
