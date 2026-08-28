<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateCouponAction
{
    public function __construct(
        private readonly SyncCouponUsersAction   $syncCouponUsersAction,
        private readonly SyncCouponTargetsAction $syncCouponTargetsAction,
        private readonly EnsureSingleFloatingCouponAction $ensureSingleFloatingCouponAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(array $data, ?User $creator = null): Coupon
    {
        return DB::transaction(function () use ($data, $creator) {
            $coupon = Coupon::create([
                ...$this->couponAttributes($data),
                'created_by_user_id' => $creator?->id,
            ]);

            $this->syncCouponUsersAction->execute(
                coupon: $coupon,
                userIds: $data['user_ids'] ?? []
            );

            $this->syncCouponTargetsAction->execute(
                coupon: $coupon,
                data: $data
            );

            $this->ensureSingleFloatingCouponAction->execute($coupon);

            return $coupon->refresh();
        });
    }

    private function couponAttributes(array $data): array
    {
        $type = $data['type'] instanceof CouponTypeEnum
            ? $data['type']->value
            : $data['type'];

        return [
            'status' => $data['status'],
            'title' => $data['title'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,

            'type' => $type,
            'value' => (int)$data['value'],

            'max_discount_amount' => $type === CouponTypeEnum::PERCENTAGE->value
                ? ($data['max_discount_amount'] ?? null)
                : null,

            'minimum_order_amount' => (int)($data['minimum_order_amount'] ?? 0),

            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,

            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'is_floating_box_enabled' => (bool)($data['is_floating_box_enabled'] ?? false),

            'scope' => $data['scope'],

            'applies_to_all_users' => (bool)$data['applies_to_all_users'],
            'applies_to_addons' => (bool)$data['applies_to_addons'],
        ];
    }
}
