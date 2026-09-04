<?php

namespace Tests\Feature\Application\Commerce\Checkout;

use App\Application\Commerce\Actions\Checkout\PayOrderWithWalletAction;
use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Financial\Exceptions\InsufficientWalletBalanceException;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayOrderWithWalletActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_snapshots_order_tax_in_payment_and_wallet_ledger(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'TEST-WALLET-TAX',
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 100_000,
            'discount_amount' => 0,
            'taxable_amount' => 100_000,
            'tax_rate_basis_points' => 1_000,
            'tax_amount' => 10_000,
            'total_amount' => 110_000,
        ]);

        $wallet = app(GetOrCreateWalletAction::class)->execute($user);

        app(CreditWalletAction::class)->execute(new CreditWalletDTO(
            wallet: $wallet,
            type: WalletTransactionTypeEnum::TOP_UP,
            amount: 200_000,
            idempotencyKey: 'test-wallet-tax-credit',
            actor: $user,
        ));

        $payment = app(PayOrderWithWalletAction::class)->execute($order, $user);

        $this->assertSame(110_000, $payment->amount);
        $this->assertSame(10_000, $payment->tax_amount);
        $this->assertSame(10_000, (int)data_get($payment->request_payload, 'tax_amount'));

        $this->assertDatabaseHas('wallet_transactions', [
            'idempotency_key' => 'order-wallet-payment:' . $order->id,
            'amount' => 110_000,
            'tax_amount' => 10_000,
        ]);

        $this->assertSame(90_000, $wallet->refresh()->balance);
    }

    public function test_it_pays_order_completely_using_wallet(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'TEST-WALLET-1',
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 300_000,
            'discount_amount' => 0,
            'total_amount' => 300_000,
        ]);

        $wallet = app(GetOrCreateWalletAction::class)->execute($user);

        app(CreditWalletAction::class)->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-wallet-payment-credit:1',
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        $payment = app(PayOrderWithWalletAction::class)->execute(
            order: $order,
            user: $user,
        );

        $wallet->refresh();
        $order->refresh();
        $payment->refresh();

        $this->assertSame(
            OrderStatusEnum::PAID,
            $order->status,
        );

        $this->assertSame(
            PaymentGatewayEnum::WALLET,
            $payment->gateway,
        );

        $this->assertSame(
            PaymentStatusEnum::PAID,
            $payment->status,
        );

        $this->assertSame(
            300_000,
            $payment->amount,
        );

        $this->assertSame(
            200_000,
            $wallet->balance,
        );

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => WalletTransactionTypeEnum::ORDER_PAYMENT->value,
            'amount' => 300_000,
            'balance_before' => 500_000,
            'balance_after' => 200_000,
            'reference_type' => $order->getMorphClass(),
            'reference_id' => $order->id,
            'idempotency_key' => 'order-wallet-payment:' . $order->id,
        ]);
    }

    public function test_wallet_order_payment_is_idempotent(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'TEST-WALLET-2',
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 300_000,
            'discount_amount' => 0,
            'total_amount' => 300_000,
        ]);

        $wallet = app(GetOrCreateWalletAction::class)->execute($user);

        app(CreditWalletAction::class)->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-wallet-payment-credit:2',
                actor: $user,
            ),
        );

        $firstPayment = app(PayOrderWithWalletAction::class)->execute($order, $user);

        $secondPayment = app(PayOrderWithWalletAction::class)->execute($order, $user);

        $wallet->refresh();

        $this->assertSame(
            $firstPayment->id,
            $secondPayment->id,
        );

        $this->assertSame(
            200_000,
            $wallet->balance,
        );

        $this->assertDatabaseCount(
            'wallet_transactions',
            2,
        );
    }

    public function test_it_does_not_change_anything_when_wallet_balance_is_insufficient(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'TEST-WALLET-3',
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 300_000,
            'discount_amount' => 0,
            'total_amount' => 300_000,
        ]);

        $wallet = app(GetOrCreateWalletAction::class)->execute($user);

        app(CreditWalletAction::class)->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 100_000,
                idempotencyKey: 'test-wallet-payment-credit:3',
                actor: $user,
            ),
        );

        try {
            app(PayOrderWithWalletAction::class)->execute($order, $user);

            $this->fail(
                'InsufficientWalletBalanceException was not thrown.'
            );
        } catch (InsufficientWalletBalanceException) {
            //
        }

        $wallet->refresh();
        $order->refresh();

        $this->assertSame(
            100_000,
            $wallet->balance,
        );

        $this->assertSame(
            OrderStatusEnum::PENDING_PAYMENT,
            $order->status,
        );

        $this->assertDatabaseMissing('payments', [
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'gateway' => PaymentGatewayEnum::WALLET->value,
        ]);

        $this->assertDatabaseMissing('wallet_transactions', [
            'idempotency_key' => 'order-wallet-payment:' . $order->id,
        ]);
    }
}
