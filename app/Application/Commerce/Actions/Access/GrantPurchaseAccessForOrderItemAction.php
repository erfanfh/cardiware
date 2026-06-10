<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Support\Carbon;

class GrantPurchaseAccessForOrderItemAction
{
    /**
     * Grant purchase access for an order item if it is a ProductSaleItem.
     */
    public function execute(Order $order, OrderItem $orderItem): ?PurchaseAccess
    {
        $purchasable = $orderItem->purchasable;

        if (!$purchasable instanceof ProductSaleItem) {
            return null;
        }

        $existingAccess = PurchaseAccess::query()
            ->where('order_item_id', $orderItem->id)
            ->first();

        if ($existingAccess instanceof PurchaseAccess) {
            return $existingAccess;
        }

        return PurchaseAccess::query()->create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $purchasable->product_id,
            'product_sale_item_id' => $purchasable->id,
            'status' => PurchaseAccessStatusEnum::ACTIVE,
            'download_limit' => $purchasable->download_limit,
            'starts_at' => now(),
            'expires_at' => $this->resolveExpiresAt($purchasable),
            'metadata' => [
                'source' => 'order_paid',
                'order_number' => $order->order_number,
                'order_item_snapshot' => $orderItem->snapshot,
            ],
        ]);
    }

    /**
     * Resolve access expiration date from product sale item.
     */
    private function resolveExpiresAt(ProductSaleItem $productSaleItem): ?Carbon
    {
        if ($productSaleItem->download_expires_in_days === null) {
            return null;
        }

        if ((int)$productSaleItem->download_expires_in_days <= 0) {
            return null;
        }

        return now()->addDays((int)$productSaleItem->download_expires_in_days);
    }
}
