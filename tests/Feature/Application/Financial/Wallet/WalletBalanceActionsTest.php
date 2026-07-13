<?php

namespace Tests\Feature\Application\Financial\Wallet;

use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\DebitWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletData;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\InsufficientWalletBalanceException;
use App\Application\Financial\Exceptions\InsufficientWithdrawableBalanceException;
use App\Application\Financial\Exceptions\WalletIdempotencyConflictException;
use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletBalanceActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_credit_withdrawable_balance(): void
    {
        [$user, $wallet] = $this->createWallet();

        $transaction = app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-top-up:1',
                withdrawableAmount: 500_000,
                actor: $user,
            ),
        );

        $wallet->refresh();

        $this->assertSame(500_000, $wallet->balance);
        $this->assertSame(500_000, $wallet->withdrawable_balance);

        $this->assertSame(
            WalletTransactionDirectionEnum::CREDIT,
            $transaction->direction,
        );

        $this->assertSame(0, $transaction->balance_before);
        $this->assertSame(500_000, $transaction->balance_after);

        $this->assertSame(
            0,
            $transaction->withdrawable_balance_before,
        );

        $this->assertSame(
            500_000,
            $transaction->withdrawable_balance_after,
        );
    }

    public function test_it_can_credit_non_withdrawable_bonus(): void
    {
        [$user, $wallet] = $this->createWallet();

        app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::BONUS,
                amount: 200_000,
                idempotencyKey: 'test-bonus:1',
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        $wallet->refresh();

        $this->assertSame(200_000, $wallet->balance);

        $this->assertSame(
            0,
            $wallet->withdrawable_balance,
        );

        $this->assertSame(
            200_000,
            $wallet->available_non_withdrawable_balance,
        );
    }

    public function test_credit_is_idempotent(): void
    {
        [$user, $wallet] = $this->createWallet();

        $data = new CreditWalletData(
            wallet: $wallet,
            type: WalletTransactionTypeEnum::TOP_UP,
            amount: 300_000,
            idempotencyKey: 'test-top-up:idempotent',
            withdrawableAmount: 300_000,
            actor: $user,
        );

        $firstTransaction =
            app(CreditWalletAction::class)->execute($data);

        $secondTransaction =
            app(CreditWalletAction::class)->execute($data);

        $wallet->refresh();

        $this->assertSame(
            $firstTransaction->id,
            $secondTransaction->id,
        );

        $this->assertSame(300_000, $wallet->balance);

        $this->assertDatabaseCount(
            'wallet_transactions',
            1,
        );
    }

    public function test_reusing_idempotency_key_for_another_amount_fails(): void
    {
        [$user, $wallet] = $this->createWallet();

        app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 300_000,
                idempotencyKey: 'test-top-up:conflict',
                withdrawableAmount: 300_000,
                actor: $user,
            ),
        );

        $this->expectException(
            WalletIdempotencyConflictException::class,
        );

        app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 400_000,
                idempotencyKey: 'test-top-up:conflict',
                withdrawableAmount: 400_000,
                actor: $user,
            ),
        );
    }

    public function test_order_payment_consumes_non_withdrawable_balance_first(): void
    {
        [$user, $wallet] = $this->createWallet();

        $creditAction = app(CreditWalletAction::class);

        $creditAction->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-top-up:order-payment',
                withdrawableAmount: 500_000,
                actor: $user,
            ),
        );

        $creditAction->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::BONUS,
                amount: 200_000,
                idempotencyKey: 'test-bonus:order-payment',
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        $transaction = app(DebitWalletAction::class)->execute(
            new DebitWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-payment:1',
                policy: WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,
                actor: $user,
            ),
        );

        $wallet->refresh();

        $this->assertSame(400_000, $wallet->balance);

        $this->assertSame(
            400_000,
            $wallet->withdrawable_balance,
        );

        /*
         * 200,000 non-withdrawable + 100,000 withdrawable.
         */
        $this->assertSame(
            100_000,
            $transaction->withdrawable_amount,
        );
    }

    public function test_withdrawable_only_debit_rejects_insufficient_withdrawable_balance(): void
    {
        [$user, $wallet] = $this->createWallet();

        app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::BONUS,
                amount: 500_000,
                idempotencyKey: 'test-bonus:withdrawal',
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        $this->expectException(
            InsufficientWithdrawableBalanceException::class,
        );

        app(DebitWalletAction::class)->execute(
            new DebitWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::WITHDRAWAL,
                amount: 100_000,
                idempotencyKey: 'test-withdrawal:1',
                policy: WalletDebitPolicyEnum::WITHDRAWABLE_ONLY,
                actor: $user,
            ),
        );
    }

    public function test_debit_respects_held_balance(): void
    {
        [$user, $wallet] = $this->createWallet();

        app(CreditWalletAction::class)->execute(
            new CreditWalletData(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-top-up:held',
                withdrawableAmount: 500_000,
                actor: $user,
            ),
        );

        $wallet->forceFill([
            'held_balance' => 400_000,
            'held_withdrawable_balance' => 400_000,
        ])->save();

        $this->expectException(
            InsufficientWalletBalanceException::class,
        );

        app(DebitWalletAction::class)->execute(
            new DebitWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::ORDER_PAYMENT,
                amount: 200_000,
                idempotencyKey: 'test-order-payment:held',
                policy: WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,
                actor: $user,
            ),
        );
    }

    /**
     * @return array{0: User, 1: Wallet}
     */
    private function createWallet(): array
    {
        $user = User::factory()->create();

        $wallet = app(GetOrCreateWalletAction::class)
            ->execute($user);

        return [$user, $wallet];
    }
}
