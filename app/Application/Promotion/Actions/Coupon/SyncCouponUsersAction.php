<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Audit\AuditService;

class SyncCouponUsersAction
{
    public function execute(Coupon $coupon, array $userIds): void
    {
        if ($coupon->applies_to_all_users) {
            app(AuditService::class)->sync($coupon, 'users', []);

            return;
        }

        $userIds = collect($userIds)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        app(AuditService::class)->sync($coupon, 'users', $userIds);
    }
}
