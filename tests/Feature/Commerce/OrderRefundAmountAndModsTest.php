<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Application\Launcher\Actions\FindRelatedLauncherModOrdersAction;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherModGrant;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Http\Middleware\EnsureUserIsAdmin;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * مرجوعی با مبلغ دلخواه، مرجوعی مادهای پولیِ یک محصول همراه آن، و کسر
 * خودکار امتیاز خرید بعد از مرجوعی.
 */
class OrderRefundAmountAndModsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_refund_amount_is_credited_to_the_wallet(): void
    {
        $order = $this->paidOrder($this->buyer(), 100000);

        app(UpdateOrderStatusAction::class)->execute(
            $order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::WALLET, refundAmount: 40000,
        );

        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertSame(40000, $order->fresh()->refund->amount);
        $this->assertSame(40000, (int) Wallet::query()->where('user_id', $order->user_id)->value('balance'));
    }

    public function test_zero_amount_refund_moves_no_money(): void
    {
        $order = $this->paidOrder($this->buyer(), 100000);

        app(UpdateOrderStatusAction::class)->execute(
            $order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::WALLET, refundAmount: 0,
        );

        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertSame(0, $order->fresh()->refund->amount);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_refund_amount_above_the_paid_amount_is_rejected(): void
    {
        $order = $this->paidOrder($this->buyer(), 100000);

        try {
            app(UpdateOrderStatusAction::class)->execute(
                $order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::WALLET, refundAmount: 100001,
            );
            $this->fail('Refund above the paid amount must be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Refund amount is out of range.', $exception->getMessage());
        }

        $this->assertSame(OrderStatusEnum::PAID, $order->fresh()->status);
        $this->assertDatabaseCount('order_refunds', 0);
    }

    public function test_admin_refunds_the_product_and_its_paid_mods_with_custom_amounts(): void
    {
        $buyer = $this->buyer();
        [$saleItemId, $modId] = $this->productWithPaidMod();

        $productOrder = $this->paidOrder($buyer, 500000, 'product_sale_item', $saleItemId);
        $modOrder = $this->paidOrder($buyer, 150000, 'launcher_mod', $modId);
        LauncherModGrant::query()->create([
            'user_id' => $buyer->id, 'launcher_mod_id' => $modId, 'source' => 'purchase', 'order_id' => $modOrder->id,
        ]);
        // سفارش ماد کاربر دیگر نباید پیدا شود.
        $this->paidOrder($this->buyer(), 150000, 'launcher_mod', $modId);

        $related = app(FindRelatedLauncherModOrdersAction::class)->execute($productOrder);
        $this->assertSame([$modOrder->id], $related->pluck('id')->all());

        $this->actingAs($this->admin());
        $this->withoutMiddleware(EnsureUserIsAdmin::class);
        $this->put(route('admin.orders.update', $productOrder), [
            'status' => 'refunded',
            'refund_method' => 'wallet',
            'refund_amount' => 300000,
            'related_mod_orders' => [$modOrder->id => ['refund' => '1', 'amount' => 50000]],
        ])->assertRedirect(route('admin.orders.edit', $productOrder))->assertSessionHasNoErrors();

        $this->assertSame(OrderStatusEnum::REFUNDED, $productOrder->fresh()->status);
        $this->assertSame(300000, $productOrder->fresh()->refund->amount);
        $this->assertSame(OrderStatusEnum::REFUNDED, $modOrder->fresh()->status);
        $this->assertSame(50000, $modOrder->fresh()->refund->amount);
        $this->assertSame(350000, (int) Wallet::query()->where('user_id', $buyer->id)->value('balance'));
        $this->assertSame(0, LauncherModGrant::query()->where('user_id', $buyer->id)->count());
    }

    public function test_unchecked_mod_order_is_left_alone(): void
    {
        $buyer = $this->buyer();
        [$saleItemId, $modId] = $this->productWithPaidMod();
        $productOrder = $this->paidOrder($buyer, 500000, 'product_sale_item', $saleItemId);
        $modOrder = $this->paidOrder($buyer, 150000, 'launcher_mod', $modId);

        $this->actingAs($this->admin());
        $this->withoutMiddleware(EnsureUserIsAdmin::class);
        $this->put(route('admin.orders.update', $productOrder), [
            'status' => 'refunded',
            'refund_method' => 'manual_bank_transfer',
            'related_mod_orders' => [$modOrder->id => ['refund' => '0', 'amount' => 150000]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(OrderStatusEnum::REFUNDED, $productOrder->fresh()->status);
        $this->assertSame(500000, $productOrder->fresh()->refund->amount);
        $this->assertSame(OrderStatusEnum::PAID, $modOrder->fresh()->status);
    }

    public function test_purchase_points_are_taken_back_when_the_order_is_refunded(): void
    {
        $buyer = $this->buyer();
        $order = $this->paidOrder($buyer, 100000);
        $account = PointAccount::query()->create(['user_id' => $buyer->id]);
        $account->forceFill(['balance' => 120, 'lifetime_earned' => 120, 'lifetime_xp' => 120])->save();
        PointLedgerEntry::query()->create([
            'point_account_id' => $account->id,
            'type' => 'earn',
            'direction' => 'credit',
            'reason' => 'purchase',
            'amount' => 100,
            'balance_before' => 20,
            'balance_after' => 120,
            'reference_type' => $order->getMorphClass(),
            'reference_id' => $order->id,
            'idempotency_key' => 'test:purchase:' . $order->id,
            'idempotency_fingerprint' => str_repeat('a', 64),
            'occurred_at' => now(),
        ]);

        app(UpdateOrderStatusAction::class)->execute(
            $order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::MANUAL_BANK_TRANSFER, refundAmount: 10000,
        );

        $this->assertSame(20, (int) $account->fresh()->balance);
        $this->assertSame(
            100,
            (int) PointLedgerEntry::query()->where('point_account_id', $account->id)->where('type', 'reversal')->sum('amount'),
        );
    }

    private function buyer(): User
    {
        return User::factory()->create();
    }

    private function admin(): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'title' => 'Admin ' . uniqid(), 'is_super_admin' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    /** @return array{0:int,1:int} */
    private function productWithPaidMod(): array
    {
        // فقط ستون‌های لازم برای این آزمون؛ فروشگاه و رسانه‌ها اهمیتی ندارند.
        // defer: بررسی کلید خارجی تا commit عقب می‌افتد و RefreshDatabase هرگز commit نمی‌کند.
        DB::statement('PRAGMA defer_foreign_keys = ON');
        $productId = $this->insertRow('products', [
            'store_id' => 1, 'status' => 'published', 'title' => 'PES', 'slug' => 'pes-' . uniqid(), 'type' => 'digital', 'structure' => 'simple',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $saleItemId = $this->insertRow('product_sale_items', [
            'product_id' => $productId, 'title' => 'PGL6', 'status' => 'available',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $modId = $this->insertRow('launcher_mods', [
            'product_sale_item_id' => $saleItemId, 'name' => 'Esteghlal facepack', 'slug' => 'esteghlal-' . uniqid(),
            'version' => '1.0', 'access_type' => 'purchase', 'price_toman' => 150000,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$saleItemId, $modId];
    }

    private function paidOrder(User $user, int $amount, ?string $type = null, ?int $purchasableId = null): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->getKey(),
            'order_number' => 'TEST-' . uniqid(),
            'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => $amount,
            'discount_amount' => 0,
            'total_amount' => $amount,
            'paid_at' => now(),
        ]);

        if ($type !== null) {
            $this->insertRow('order_items', [
                'order_id' => $order->id,
                'purchasable_type' => $type,
                'purchasable_id' => $purchasableId,
            ]);
        }

        return $order;
    }

    /**
     * ردیف را با مقادیر داده‌شده درج می‌کند و ستون‌های NOT NULL بدون پیش‌فرض
     * را با مقدار خنثی پر می‌کند تا آزمون به جزئیات اسکیمای بی‌ربط وابسته نباشد.
     *
     * @param array<string, mixed> $values
     */
    private function insertRow(string $table, array $values): int
    {
        foreach (DB::select("PRAGMA table_info('{$table}')") as $column) {
            if ($column->pk || ! $column->notnull || $column->dflt_value !== null || array_key_exists($column->name, $values)) {
                continue;
            }
            $type = strtolower((string) $column->type);
            $values[$column->name] = match (true) {
                str_contains($type, 'int'), str_contains($type, 'numeric'), str_contains($type, 'real') => 0,
                str_contains($type, 'date'), str_contains($type, 'time') => now(),
                default => '',
            };
        }

        return (int) DB::table($table)->insertGetId($values);
    }
}
