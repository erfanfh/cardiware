<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Models\Role;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductCommissionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([OrderPaidEvent::class]);
    }

    public function test_default_and_changed_rates_apply_only_when_each_order_is_paid(): void
    {
        $product = $this->product();
        $this->assertSame(0, $product->fresh()->seller_commission_basis_points);

        $oldOrder = $this->orderWithItem($product, 101);
        app(MarkOrderAsPaidAction::class)->execute($oldOrder);

        $oldItem = $oldOrder->items()->firstOrFail();
        $this->assertSame(0, $oldItem->sellerCommissionAmount());
        $this->assertSame(101, $oldItem->platformCommissionAmount());

        $product->update(['seller_commission_basis_points' => 2525]);
        $newOrder = $this->orderWithItem($product, 101);
        app(MarkOrderAsPaidAction::class)->execute($newOrder);

        $newItem = $newOrder->items()->firstOrFail();
        $this->assertSame(2525, $newItem->sellerCommissionBasisPoints());
        $this->assertSame(7475, $newItem->platformCommissionBasisPoints());
        $this->assertSame(26, $newItem->sellerCommissionAmount());
        $this->assertSame(75, $newItem->platformCommissionAmount());
        $this->assertNotNull($newItem->commission_snapshotted_at);

        $product->update(['seller_commission_basis_points' => 10000]);
        app(MarkOrderAsPaidAction::class)->execute($newOrder->fresh());
        $this->assertSame(26, $newItem->fresh()->sellerCommissionAmount());
        $this->assertSame(0, $oldItem->fresh()->sellerCommissionAmount());
    }

    public function test_manual_paid_transition_snapshots_net_amount_and_preserves_it_on_repayment(): void
    {
        $product = $this->product();
        $product->update(['seller_commission_basis_points' => 5000]);
        $order = $this->orderWithItem($product, 80);

        app(UpdateOrderStatusAction::class)->execute($order, OrderStatusEnum::PAID);
        $item = $order->items()->firstOrFail();
        $this->assertSame(40, $item->sellerCommissionAmount());
        $this->assertSame(40, $item->platformCommissionAmount());

        app(UpdateOrderStatusAction::class)->execute($order->fresh(), OrderStatusEnum::FAILED);
        $product->update(['seller_commission_basis_points' => 0]);
        app(UpdateOrderStatusAction::class)->execute($order->fresh(), OrderStatusEnum::PAID);
        $this->assertSame(40, $item->fresh()->sellerCommissionAmount());
    }

    public function test_pending_order_uses_the_rate_at_payment_including_product_addons(): void
    {
        $product = $this->product();
        $order = $this->orderWithItem($product, 80);
        $product->update(['seller_commission_basis_points' => 5000]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'parent_order_item_id' => $order->items()->firstOrFail()->id,
            'purchasable_type' => 'product_addon',
            'purchasable_id' => 1,
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'quantity' => 1,
            'unit_price' => 20,
            'subtotal_amount' => 20,
            'total_amount' => 20,
            'final_total_amount' => 20,
        ]);
        $order->update(['subtotal_amount' => 100, 'total_amount' => 100]);

        app(MarkOrderAsPaidAction::class)->execute($order);

        $this->assertSame(50, $order->items->sum(fn (OrderItem $item) => $item->sellerCommissionAmount()));
        $this->assertSame(50, $order->items->sum(fn (OrderItem $item) => $item->platformCommissionAmount()));
    }

    public function test_historical_paid_item_without_snapshot_remains_platform_owned(): void
    {
        $product = $this->product();
        $order = $this->orderWithItem($product, 90);
        $order->update(['status' => OrderStatusEnum::PAID, 'paid_at' => now()]);
        $product->update(['seller_commission_basis_points' => 6000]);

        $item = $order->items()->firstOrFail();
        $this->assertNull($item->commission_snapshotted_at);
        $this->assertSame(0, $item->sellerCommissionAmount());
        $this->assertSame(90, $item->platformCommissionAmount());
        app(MarkOrderAsPaidAction::class)->execute($order->fresh());
        $this->assertNull($item->fresh()->commission_snapshotted_at);
    }

    public function test_migration_backfills_preexisting_paid_items_as_platform_owned(): void
    {
        $migration = require base_path('database/migrations/2026_10_02_000001_add_product_commission_snapshots.php');
        $migration->down();

        $product = $this->product();
        $paidOrder = $this->orderWithItem($product, 90);
        $paidOrder->update(['status' => OrderStatusEnum::PAID, 'paid_at' => now()]);
        $pendingOrder = $this->orderWithItem($product, 40);

        $migration->up();

        $paidItem = $paidOrder->items()->firstOrFail();
        $pendingItem = $pendingOrder->items()->firstOrFail();
        $this->assertSame(0, $paidItem->sellerCommissionAmount());
        $this->assertSame(90, $paidItem->platformCommissionAmount());
        $this->assertNotNull($paidItem->commission_snapshotted_at);
        $this->assertNull($pendingItem->commission_snapshotted_at);
    }

    public function test_admin_can_set_a_product_rate_and_invalid_rates_are_rejected(): void
    {
        $product = $this->product();
        $role = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('تقسیم درآمد محصول');

        $payload = [
            'title' => $product->title,
            'body' => '',
            'slug' => $product->slug,
            'store_id' => $product->store_id,
            'type' => ProductTypeEnum::DIGITAL->value,
            'structure' => ProductStructureEnum::SIMPLE->value,
            'status' => ProductStatusEnum::ACTIVE->value,
            'seller_commission_percent' => '37.25',
        ];

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $payload)
            ->assertRedirect();

        $this->assertSame(3725, $product->fresh()->seller_commission_basis_points);

        $this->put(route('admin.products.update', $product), [
            ...$payload,
            'seller_commission_percent' => '100.01',
        ])->assertSessionHasErrors('seller_commission_percent');

        $this->assertSame(3725, $product->fresh()->seller_commission_basis_points);
    }

    private function product(): Product
    {
        $store = Store::query()->create([
            'title' => 'Store',
            'slug' => 'commission-store',
            'status' => StoreStatusEnum::ACTIVE,
        ]);

        return Product::query()->create([
            'store_id' => $store->id,
            'title' => 'Product',
            'slug' => 'commission-product',
            'type' => ProductTypeEnum::DIGITAL,
            'structure' => ProductStructureEnum::SIMPLE,
            'status' => ProductStatusEnum::ACTIVE,
            'published_at' => now(),
        ]);
    }

    private function orderWithItem(Product $product, int $finalAmount): Order
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'COM-' . uniqid(),
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => $finalAmount,
            'total_amount' => $finalAmount,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'purchasable_type' => 'product_sale_item',
            'purchasable_id' => 1,
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'quantity' => 1,
            'unit_price' => $finalAmount,
            'subtotal_amount' => $finalAmount,
            'total_amount' => $finalAmount,
            'final_total_amount' => $finalAmount,
        ]);

        return $order;
    }
}
