<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;

class RevokePurchaseAccessesForOrderAction
{
    public function execute(Order $order, string $reason = 'order_status_no_longer_grants_access'): void
    {
        $accesses = PurchaseAccess::query()
            ->where('order_id', $order->id)
            ->get();

        foreach ($accesses as $access) {
            $access->forceFill([
                'status' => PurchaseAccessStatusEnum::REVOKED,
                'metadata' => array_merge($access->metadata ?? [], [
                    'revoked_at' => now()->toDateTimeString(),
                    'revoked_reason' => $reason,
                    'revoked_by_order_status' => $order->status->value,
                ]),
            ])->save();
        }
    }
}
