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
     * Grant or reactivate purchase access for an order item if it is a ProductSaleItem.
     */
    public function execute(Order $order, OrderItem $orderItem): ?PurchaseAccess
    {
        $purchasable = $orderItem->purchasable;

        if (!$purchasable instanceof ProductSaleItem) {
            return null;
        }

        $existingAccess = PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->where('order_item_id', $orderItem->id)
            ->first();

        if ($existingAccess instanceof PurchaseAccess) {
            return $this->handleExistingAccess(
                access: $existingAccess,
                order: $order,
                orderItem: $orderItem,
                productSaleItem: $purchasable,
            );
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

    private function handleExistingAccess(
        PurchaseAccess  $access,
        Order           $order,
        OrderItem       $orderItem,
        ProductSaleItem $productSaleItem,
    ): PurchaseAccess
    {
        if ($access->status === PurchaseAccessStatusEnum::ACTIVE) {
            return $access->refresh();
        }

        if ($access->status === PurchaseAccessStatusEnum::EXPIRED) {
            return $access->refresh();
        }

        if ($access->status === PurchaseAccessStatusEnum::REVOKED) {
            $access->forceFill([
                'status' => PurchaseAccessStatusEnum::ACTIVE,
                'download_limit' => $productSaleItem->download_limit,
                'starts_at' => now(),
                'expires_at' => $this->resolveExpiresAt($productSaleItem),
                'metadata' => array_merge($access->metadata ?? [], [
                    'reactivated_at' => now()->toDateTimeString(),
                    'reactivated_reason' => 'order_status_grants_access',
                    'reactivated_by_order_status' => $order->status->value,
                    'order_item_snapshot' => $orderItem->snapshot,
                ]),
            ])->save();

            return $access->refresh();
        }

        return $access->refresh();
    }

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
