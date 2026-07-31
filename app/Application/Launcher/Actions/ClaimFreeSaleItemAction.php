<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Actions\Access\GrantPurchaseAccessForOrderItemAction;
use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Application\Launcher\Exceptions\LauncherCommerceException;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClaimFreeSaleItemAction
{
    public function __construct(
        private readonly ResolvePurchasablePriceAction $resolvePrice,
        private readonly GrantPurchaseAccessForOrderItemAction $grantAccess,
    ) {}

    public function execute(User $user, int $saleItemId, string $idempotencyKey): PurchaseAccess
    {
        return DB::transaction(function () use ($user, $saleItemId, $idempotencyKey): PurchaseAccess {
            $saleItem = ProductSaleItem::query()
                ->whereKey($saleItemId)
                ->where('launcher_enabled', true)
                ->lockForUpdate()
                ->with('product')
                ->firstOrFail();

            $existing = PurchaseAccess::query()
                ->withoutGlobalScopes()
                ->where('user_id', $user->getKey())
                ->where('product_sale_item_id', $saleItem->getKey())
                ->latest('id')
                ->get()
                ->first(fn(PurchaseAccess $access): bool => $access->isUsable());

            if ($existing instanceof PurchaseAccess) {
                return $existing;
            }

            if (!$saleItem->isPurchasableAvailable()
                || $this->resolvePrice->execute($saleItem)->finalPrice !== 0) {
                throw LauncherCommerceException::notFree();
            }

            $order = Order::query()->create([
                'user_id' => $user->getKey(),
                'order_number' => 'LF-' . Str::upper(Str::random(20)),
                'status' => OrderStatusEnum::PAID,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_at' => now(),
                'metadata' => [
                    'source' => 'launcher_free_claim',
                    'idempotency_key' => $idempotencyKey,
                ],
            ]);

            $orderItem = OrderItem::query()->create([
                'order_id' => $order->getKey(),
                'purchasable_type' => 'product_sale_item',
                'purchasable_id' => $saleItem->getKey(),
                'product_id' => $saleItem->product_id,
                'store_id' => $saleItem->product?->store_id,
                'quantity' => 1,
                'unit_price' => 0,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'coupon_discount_amount' => 0,
                'total_amount' => 0,
                'final_total_amount' => 0,
                'snapshot' => $saleItem->getPurchasableSnapshot(),
                'metadata' => ['source' => 'launcher_free_claim'],
            ]);

            $orderItem->setRelation('purchasable', $saleItem);

            return $this->grantAccess->execute($order, $orderItem);
        });
    }
}
