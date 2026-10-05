<?php

namespace App\Application\Vip\Actions;

use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class GrantVipSubscriptionsForOrderAction
{
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->isPaid()) {
                return;
            }

            $items = $order->items()
                ->where('purchasable_type', (new VipPlan)->getMorphClass())
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            // Serializes renewals from different paid orders for the same user.
            User::query()->whereKey($order->user_id)->lockForUpdate()->firstOrFail();

            foreach ($items as $item) {
                $existing = VipSubscription::query()->where('order_item_id', $item->id)->first();
                if ($existing && $existing->revoked_at === null) {
                    continue;
                }

                $days = (int) data_get($item->snapshot, 'duration_days');
                if ($days < 1 || $days > 36500 || (int) $item->quantity !== 1) {
                    throw new DomainException('VIP order item has an invalid duration or quantity.');
                }

                $now = CarbonImmutable::now();
                $latestEnd = VipSubscription::query()
                    ->where('user_id', $order->user_id)
                    ->whereNull('revoked_at')
                    ->max('expires_at');
                $start = $latestEnd && CarbonImmutable::parse($latestEnd)->greaterThan($now)
                    ? CarbonImmutable::parse($latestEnd)
                    : $now;

                $attributes = [
                    'user_id' => $order->user_id,
                    'vip_plan_id' => $item->purchasable_id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'duration_days' => $days,
                    'starts_at' => $start,
                    'expires_at' => $start->addDays($days),
                    'revoked_at' => null,
                ];

                if ($existing) {
                    $existing->forceFill($attributes)->save();
                } else {
                    VipSubscription::query()->create($attributes);
                }
            }
        }, 3);
    }
}
