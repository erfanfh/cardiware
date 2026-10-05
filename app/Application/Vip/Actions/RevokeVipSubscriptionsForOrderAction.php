<?php

namespace App\Application\Vip\Actions;

use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RevokeVipSubscriptionsForOrderAction
{
    public function execute(Order $order): void
    {
        if (! $order->items()
            ->where('purchasable_type', (new VipPlan)->getMorphClass())
            ->exists()) {
            return;
        }

        DB::transaction(function () use ($order): void {
            User::query()->whereKey($order->user_id)->lockForUpdate()->firstOrFail();

            $now = CarbonImmutable::now();
            $revokedCount = VipSubscription::query()
                ->where('order_id', $order->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => $now]);

            if ($revokedCount === 0) {
                return;
            }

            // Move queued renewals forward so a refunded interval leaves no gap.
            $latestActiveEnd = VipSubscription::query()
                ->where('user_id', $order->user_id)
                ->whereNull('revoked_at')
                ->where('starts_at', '<=', $now)
                ->where('expires_at', '>', $now)
                ->max('expires_at');
            $nextStart = $latestActiveEnd ? CarbonImmutable::parse($latestActiveEnd) : $now;

            $queued = VipSubscription::query()
                ->where('user_id', $order->user_id)
                ->whereNull('revoked_at')
                ->where('starts_at', '>', $now)
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get();

            foreach ($queued as $subscription) {
                $subscription->forceFill([
                    'starts_at' => $nextStart,
                    'expires_at' => $nextStart->addDays($subscription->duration_days),
                ])->save();
                $nextStart = $nextStart->addDays($subscription->duration_days);
            }
        }, 3);
    }
}
