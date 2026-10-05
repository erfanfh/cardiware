<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Http\Middleware\EnsureUserIsAdmin;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOrderRefundTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([OrderPaidEvent::class, OrderPaymentRevokedEvent::class]);
        $this->createTables();
    }

    public function test_wallet_refund_credits_full_amount_once_and_records_the_method(): void
    {
        $order = $this->paidOrder();
        $admin = $this->user();

        $action = app(UpdateOrderStatusAction::class);
        $action->execute($order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::WALLET, $admin);
        $action->execute($order->fresh(), OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::WALLET, $admin);

        $refund = $order->fresh()->refund;
        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertSame(OrderRefundMethodEnum::WALLET, $refund->method);
        $this->assertSame(100000, $refund->amount);
        $this->assertSame(100000, Wallet::query()->where('user_id', $order->user_id)->value('balance'));
        $this->assertSame(0, Wallet::query()->where('user_id', $order->user_id)->value('withdrawable_balance'));
        $this->assertDatabaseCount('order_refunds', 1);
        $this->assertSame(1, WalletTransaction::query()->where('type', WalletTransactionTypeEnum::ORDER_REFUND)->count());
        $this->assertSame($refund->wallet_transaction_id, WalletTransaction::query()->first()->getKey());
    }

    public function test_manual_refund_needs_no_extra_fields_and_never_credits_the_wallet(): void
    {
        $order = $this->paidOrder();
        $action = app(UpdateOrderStatusAction::class);

        $action->execute(
            $order,
            OrderStatusEnum::REFUNDED,
            OrderRefundMethodEnum::MANUAL_BANK_TRANSFER,
        );

        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertNull($order->fresh()->refund->manual_reference);
        $this->assertNull($order->fresh()->refund->wallet_transaction_id);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_unavailable_wallet_rolls_back_status_and_no_refund_is_recorded(): void
    {
        $order = $this->paidOrder();
        Wallet::query()->create([
            'user_id' => $order->user_id,
            'status' => WalletStatusEnum::BLOCKED,
        ]);

        try {
            app(UpdateOrderStatusAction::class)->execute(
                $order,
                OrderStatusEnum::REFUNDED,
                OrderRefundMethodEnum::WALLET,
            );
            $this->fail('Blocked wallet should reject an automatic refund.');
        } catch (DomainException $exception) {
            $this->assertSame('The user wallet is unavailable.', $exception->getMessage());
        }

        $this->assertSame(OrderStatusEnum::PAID, $order->fresh()->status);
        $this->assertDatabaseCount('order_refunds', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_recorded_refund_cannot_be_changed_back_to_paid(): void
    {
        $order = $this->paidOrder();
        $action = app(UpdateOrderStatusAction::class);
        $action->execute($order, OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::MANUAL_BANK_TRANSFER);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Refunded order cannot be marked as paid again.');
        $action->execute($order->fresh(), OrderStatusEnum::PAID);
    }

    public function test_paid_order_requires_a_refund_method_before_money_is_moved(): void
    {
        $order = $this->paidOrder();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Refund method is required.');

        try {
            app(UpdateOrderStatusAction::class)->execute($order, OrderStatusEnum::REFUNDED);
        } finally {
            $this->assertSame(OrderStatusEnum::PAID, $order->fresh()->status);
            $this->assertDatabaseCount('order_refunds', 0);
            $this->assertDatabaseCount('wallet_transactions', 0);
        }
    }

    public function test_free_paid_order_can_still_be_marked_refunded_without_money_movement(): void
    {
        $order = $this->paidOrder();
        $order->update(['total_amount' => 0]);

        app(UpdateOrderStatusAction::class)->execute($order, OrderStatusEnum::REFUNDED);

        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertDatabaseCount('order_refunds', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_admin_request_requires_only_the_refund_method(): void
    {
        $order = $this->paidOrder();
        $this->actingAs($this->user());
        $this->withoutMiddleware(EnsureUserIsAdmin::class);

        $this->put(route('admin.orders.update', $order), [
            'status' => OrderStatusEnum::REFUNDED->value,
        ])->assertSessionHasErrors('refund_method');

        $this->put(route('admin.orders.update', $order), [
            'status' => OrderStatusEnum::REFUNDED->value,
            'refund_method' => OrderRefundMethodEnum::MANUAL_BANK_TRANSFER->value,
        ])->assertRedirect(route('admin.orders.edit', $order));

        $this->assertSame(OrderStatusEnum::REFUNDED, $order->fresh()->status);
        $this->assertSame(OrderRefundMethodEnum::MANUAL_BANK_TRANSFER, $order->fresh()->refund->method);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    private function paidOrder(): Order
    {
        $user = $this->user();

        return Order::query()->create([
            'user_id' => $user->getKey(),
            'order_number' => 'TEST-REFUND-' . $user->getKey(),
            'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'paid_at' => now(),
        ]);
    }

    private function user(): User
    {
        return User::query()->create([
            'username' => 'refund-user-' . (User::query()->count() + 1),
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'refund-' . (User::query()->count() + 1) . '@example.test',
            'password' => 'password',
            'status' => UserStatusEnum::ACTIVE,
            'role_id' => 1,
        ]);
    }

    private function createTables(): void
    {
        foreach (['order_refunds', 'purchase_accesses', 'order_items', 'wallet_transactions', 'wallets', 'orders', 'users', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();
        });
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => 1,
            'title' => 'Test administrator',
            'is_super_admin' => true,
        ]);

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('status');
            $table->foreignId('role_id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('order_number')->unique();
            $table->string('status');
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('taxable_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->string('purchasable_type');
            $table->unsignedBigInteger('purchasable_id');
        });

        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('withdrawable_balance')->default(0);
            $table->unsignedBigInteger('held_balance')->default(0);
            $table->unsignedBigInteger('held_withdrawable_balance')->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('public_token')->unique();
            $table->foreignId('wallet_id');
            $table->foreignId('actor_user_id')->nullable();
            $table->string('type');
            $table->string('direction');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('withdrawable_amount')->default(0);
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->unsignedBigInteger('withdrawable_balance_before');
            $table->unsignedBigInteger('withdrawable_balance_after');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key')->unique();
            $table->char('idempotency_fingerprint', 64);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('purchase_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
        });

        $migration = require database_path('migrations/2026_09_27_000000_create_order_refunds_table.php');
        $migration->up();
    }
}
