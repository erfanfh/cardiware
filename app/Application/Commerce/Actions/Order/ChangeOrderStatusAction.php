<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use Carbon\CarbonImmutable;
use DateTimeInterface;

class ChangeOrderStatusAction
{
    public function execute(
        Order $order,
        OrderStatusEnum $newStatus,
        ?DateTimeInterface $transitionedAt = null,
    ): Order {
        if ($order->status === $newStatus) {
            return $order->refresh();
        }

        $oldStatus = $order->status;
        $transitionedAt = $transitionedAt
            ? CarbonImmutable::instance($transitionedAt)
            : now(config('app.timezone'))->toImmutable();

        // Clear an existing checkout flag without adding metadata to manual orders.
        $metadata = $order->metadata ?? [];
        if (array_key_exists('purchase_notification_eligible', $metadata)) {
            $order->metadata = array_merge($metadata, ['purchase_notification_eligible' => false]);
        }

        $order->forceFill([
            'status' => $newStatus,
            'paid_at' => $newStatus->isPaid() && ! $oldStatus->isPaid()
                ? $transitionedAt
                : $order->paid_at,
            'cancelled_at' => $newStatus === OrderStatusEnum::CANCELLED
                ? ($order->cancelled_at ?? $transitionedAt)
                : $order->cancelled_at,
        ])->save();

        return $order->refresh();
    }
}
