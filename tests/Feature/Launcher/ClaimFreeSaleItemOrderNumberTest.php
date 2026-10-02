<?php

namespace Tests\Feature\Launcher;

use App\Application\Commerce\Actions\Order\GenerateOrderNumberAction;
use App\Application\Launcher\Actions\ClaimFreeSaleItemAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClaimFreeSaleItemOrderNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_launcher_order_uses_the_shared_sequential_order_number_generator(): void
    {
        $user = User::factory()->create();
        $now = now();

        $existingOrder = Order::query()->create([
            'user_id' => $user->getKey(),
            'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'paid_at' => $now,
        ]);
        $existingOrder->update([
            'order_number' => app(GenerateOrderNumberAction::class)->execute($existingOrder),
        ]);

        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Free Claim Store',
            'slug' => 'free-claim-store',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'Free Launcher Product',
            'slug' => 'free-launcher-product',
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId,
            'sku' => 'FREE-LAUNCHER-ORDER-NUMBER',
            'base_price' => 0,
            'status' => 'available',
            'launcher_enabled' => true,
            'launcher_free_claim_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $access = app(ClaimFreeSaleItemAction::class)->execute(
            user: $user,
            saleItemId: $saleItemId,
            idempotencyKey: 'free-order-number-test',
        );

        $freeOrder = Order::query()->findOrFail($access->order_id);

        $this->assertSame('1001', $existingOrder->fresh()->order_number);
        $this->assertSame('1002', $freeOrder->order_number);
        $this->assertSame(
            app(GenerateOrderNumberAction::class)->execute($freeOrder),
            $freeOrder->order_number,
        );
        $freeItem = $freeOrder->items()->firstOrFail();
        $this->assertNotNull($freeItem->commission_snapshotted_at);
        $this->assertSame(0, $freeItem->sellerCommissionAmount());
        $this->assertSame(0, $freeItem->platformCommissionAmount());
    }
}
