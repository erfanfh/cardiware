<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use DomainException;

class OrderStatusTransitionGuard
{
    public function ensureAllowed(OrderStatusEnum $oldStatus, OrderStatusEnum $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $allowed = match ($oldStatus) {
            OrderStatusEnum::PENDING_PAYMENT => in_array($newStatus, [
                OrderStatusEnum::PAID,
                OrderStatusEnum::FAILED,
                OrderStatusEnum::CANCELLED,
            ], true),

            OrderStatusEnum::PAID => in_array($newStatus, [
                OrderStatusEnum::FAILED,
                OrderStatusEnum::CANCELLED,
                OrderStatusEnum::REFUNDED,
            ], true),

            OrderStatusEnum::FAILED => in_array($newStatus, [
                OrderStatusEnum::PENDING_PAYMENT,
                OrderStatusEnum::PAID,
                OrderStatusEnum::CANCELLED,
            ], true),

            OrderStatusEnum::CANCELLED => in_array($newStatus, [
                OrderStatusEnum::PENDING_PAYMENT,
                OrderStatusEnum::PAID,
            ], true),

            OrderStatusEnum::REFUNDED => in_array($newStatus, [
                OrderStatusEnum::PAID,
            ], true),
        };

        if (!$allowed) {
            throw new DomainException('Order status transition is not allowed.');
        }
    }
}
