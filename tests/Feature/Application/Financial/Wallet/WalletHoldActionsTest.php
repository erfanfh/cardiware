<?php

namespace Tests\Feature\Application\Financial\Wallet;

use App\Application\Financial\Actions\Wallet\CaptureWalletHoldAction;
use App\Application\Financial\Actions\Wallet\CreateWalletHoldAction;
use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\Actions\Wallet\ReleaseWalletHoldAction;
use App\Application\Financial\DTOs\CreateWalletHoldData;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Financial\Exceptions\InsufficientWithdrawableBalanceException;
use App\Application\Financial\Exceptions\WalletHoldIdempotencyConflictException;
use App\Application\Financial\Exceptions\WalletHoldUnavailableException;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletHoldActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_hold_reserves_non_withdrawable_balance_first(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $hold = app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:1',
                actor: $user,
            ),
        );

        $wallet->refresh();

        $this->assertSame(
            WalletHoldStatusEnum::ACTIVE,
            $hold->status,
        );

        $this->assertSame(700_000, $wallet->balance);
        $this->assertSame(500_000, $wallet->withdrawable_balance);

        $this->assertSame(300_000, $wallet->held_balance);

        /*
         * 200,000 non-withdrawable was reserved first,
         * so only 100,000 withdrawable was reserved.
         */
        $this->assertSame(
            100_000,
            $wallet->held_withdrawable_balance,
        );

        $this->assertSame(
            400_000,
            $wallet->available_balance,
        );

        $this->assertSame(
            400_000,
            $wallet->available_withdrawable_balance,
        );
    }

    public function test_withdrawal_hold_requires_withdrawable_balance(): void
    {
        $user = User::factory()->create();

        $wallet = app(GetOrCreateWalletAction::class)
            ->execute($user);

        app(CreditWalletAction::class)->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::BONUS,
                amount: 500_000,
                idempotencyKey: 'test-bonus:withdrawal-hold',
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        $this->expectException(
            InsufficientWithdrawableBalanceException::class,
        );

        app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::WITHDRAWAL_REQUEST,
                amount: 100_000,
                idempotencyKey: 'test-withdrawal-hold:1',
                actor: $user,
            ),
        );
    }

    public function test_hold_creation_is_idempotent(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $data = new CreateWalletHoldData(
            wallet: $wallet,
            type: WalletHoldTypeEnum::ORDER_PAYMENT,
            amount: 300_000,
            idempotencyKey: 'test-order-hold:idempotent',
            actor: $user,
        );

        $firstHold =
            app(CreateWalletHoldAction::class)->execute($data);

        $secondHold =
            app(CreateWalletHoldAction::class)->execute($data);

        $wallet->refresh();

        $this->assertSame(
            $firstHold->id,
            $secondHold->id,
        );

        $this->assertSame(
            300_000,
            $wallet->held_balance,
        );

        $this->assertDatabaseCount(
            'wallet_holds',
            1,
        );
    }

    public function test_reusing_hold_key_for_another_amount_fails(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 200_000,
                idempotencyKey: 'test-order-hold:conflict',
                actor: $user,
            ),
        );

        $this->expectException(
            WalletHoldIdempotencyConflictException::class,
        );

        app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:conflict',
                actor: $user,
            ),
        );
    }

    public function test_releasing_hold_restores_available_balance(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $hold = app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:release',
                actor: $user,
            ),
        );

        $releasedHold =
            app(ReleaseWalletHoldAction::class)->execute(
                hold: $hold,
                releasedBy: $user,
                reason: 'test_release',
            );

        $wallet->refresh();

        $this->assertSame(
            WalletHoldStatusEnum::RELEASED,
            $releasedHold->status,
        );

        $this->assertSame(700_000, $wallet->balance);
        $this->assertSame(500_000, $wallet->withdrawable_balance);

        $this->assertSame(0, $wallet->held_balance);

        $this->assertSame(
            0,
            $wallet->held_withdrawable_balance,
        );

        $this->assertDatabaseCount(
            'wallet_transactions',
            2,
        );
    }

    public function test_capturing_hold_debits_balance_and_creates_transaction(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $hold = app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:capture',
                actor: $user,
            ),
        );

        $transaction =
            app(CaptureWalletHoldAction::class)
                ->execute($hold);

        $wallet->refresh();
        $hold->refresh();

        $this->assertSame(400_000, $wallet->balance);

        $this->assertSame(
            400_000,
            $wallet->withdrawable_balance,
        );

        $this->assertSame(0, $wallet->held_balance);

        $this->assertSame(
            0,
            $wallet->held_withdrawable_balance,
        );

        $this->assertSame(
            WalletHoldStatusEnum::CAPTURED,
            $hold->status,
        );

        $this->assertSame(
            $transaction->id,
            $hold->captured_transaction_id,
        );

        $this->assertSame(
            WalletTransactionTypeEnum::ORDER_PAYMENT,
            $transaction->type,
        );

        $this->assertSame(
            100_000,
            $transaction->withdrawable_amount,
        );

        /*
         * Two credit transactions + one captured debit transaction.
         */
        $this->assertDatabaseCount(
            'wallet_transactions',
            3,
        );
    }

    public function test_capture_is_idempotent(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $hold = app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:capture-idempotent',
                actor: $user,
            ),
        );

        $firstTransaction =
            app(CaptureWalletHoldAction::class)
                ->execute($hold);

        $secondTransaction =
            app(CaptureWalletHoldAction::class)
                ->execute($hold);

        $wallet->refresh();

        $this->assertSame(
            $firstTransaction->id,
            $secondTransaction->id,
        );

        $this->assertSame(400_000, $wallet->balance);

        $this->assertDatabaseCount(
            'wallet_transactions',
            3,
        );
    }

    public function test_released_hold_cannot_be_captured(): void
    {
        [$user, $wallet] = $this->createFundedWallet();

        $hold = app(CreateWalletHoldAction::class)->execute(
            new CreateWalletHoldData(
                wallet: $wallet,
                type: WalletHoldTypeEnum::ORDER_PAYMENT,
                amount: 300_000,
                idempotencyKey: 'test-order-hold:released-capture',
                actor: $user,
            ),
        );

        app(ReleaseWalletHoldAction::class)->execute(
            hold: $hold,
            releasedBy: $user,
            reason: 'cancelled',
        );

        $this->expectException(
            WalletHoldUnavailableException::class,
        );

        app(CaptureWalletHoldAction::class)
            ->execute($hold);
    }

    /**
     * Wallet result:
     *
     * balance = 700,000
     * withdrawable = 500,000
     * non-withdrawable = 200,000
     *
     * @return array{0: User, 1: Wallet}
     */
    private function createFundedWallet(): array
    {
        $user = User::factory()->create();

        $wallet = app(GetOrCreateWalletAction::class)
            ->execute($user);

        $creditAction = app(CreditWalletAction::class);

        $creditAction->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::TOP_UP,
                amount: 500_000,
                idempotencyKey: 'test-top-up:' . $user->id,
                withdrawableAmount: 500_000,
                actor: $user,
            ),
        );

        $creditAction->execute(
            new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::BONUS,
                amount: 200_000,
                idempotencyKey: 'test-bonus:' . $user->id,
                withdrawableAmount: 0,
                actor: $user,
            ),
        );

        return [
            $user,
            $wallet->refresh(),
        ];
    }
}
