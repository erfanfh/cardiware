<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\Exceptions\InsufficientWalletBalanceException;
use App\Application\Financial\Exceptions\InsufficientWithdrawableBalanceException;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Models\Wallet;
use InvalidArgumentException;

class WalletBalanceCalculator
{
    /**
     * Ensure wallet balance buckets are internally consistent.
     */
    public function assertIntegrity(Wallet $wallet): void
    {
        $balance = (int)$wallet->balance;
        $withdrawableBalance = (int)$wallet->withdrawable_balance;

        $heldBalance = (int)$wallet->held_balance;
        $heldWithdrawableBalance = (int)$wallet->held_withdrawable_balance;

        if (
            $balance < 0
            || $withdrawableBalance < 0
            || $heldBalance < 0
            || $heldWithdrawableBalance < 0
        ) {
            throw new WalletIntegrityException(
                'Wallet balances cannot be negative.'
            );
        }

        if ($withdrawableBalance > $balance) {
            throw new WalletIntegrityException(
                'Withdrawable balance cannot exceed total balance.'
            );
        }

        if ($heldBalance > $balance) {
            throw new WalletIntegrityException(
                'Held balance cannot exceed total balance.'
            );
        }

        if ($heldWithdrawableBalance > $heldBalance) {
            throw new WalletIntegrityException(
                'Held withdrawable balance cannot exceed total held balance.'
            );
        }

        if ($heldWithdrawableBalance > $withdrawableBalance) {
            throw new WalletIntegrityException(
                'Held withdrawable balance cannot exceed withdrawable balance.'
            );
        }

        if ($this->availableWithdrawableBalance($wallet) > $this->availableBalance($wallet)) {
            throw new WalletIntegrityException(
                'Available withdrawable balance cannot exceed available balance.'
            );
        }

        if ($this->availableNonWithdrawableBalance($wallet) < 0) {
            throw new WalletIntegrityException(
                'Available non-withdrawable balance cannot be negative.'
            );
        }
    }

    public function availableBalance(Wallet $wallet): int
    {
        return (int)$wallet->balance
            - (int)$wallet->held_balance;
    }

    public function availableWithdrawableBalance(Wallet $wallet): int
    {
        return (int)$wallet->withdrawable_balance
            - (int)$wallet->held_withdrawable_balance;
    }

    public function availableNonWithdrawableBalance(Wallet $wallet): int
    {
        return $this->availableBalance($wallet)
            - $this->availableWithdrawableBalance($wallet);
    }

    /**
     * Determine how much of a debit must be taken from
     * the withdrawable balance bucket.
     */
    public function calculateWithdrawableDebit(
        Wallet                $wallet,
        int                   $amount,
        WalletDebitPolicyEnum $policy,
    ): int
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Wallet debit amount must be greater than zero.'
            );
        }

        $this->assertIntegrity($wallet);

        $availableBalance = $this->availableBalance($wallet);

        if ($amount > $availableBalance) {
            throw new InsufficientWalletBalanceException(
                'Wallet available balance is insufficient.'
            );
        }

        $availableWithdrawableBalance =
            $this->availableWithdrawableBalance($wallet);

        $availableNonWithdrawableBalance =
            $this->availableNonWithdrawableBalance($wallet);

        return match ($policy) {
            WalletDebitPolicyEnum::WITHDRAWABLE_ONLY =>
            $this->withdrawableOnlyAmount(
                amount: $amount,
                availableWithdrawableBalance: $availableWithdrawableBalance,
            ),

            WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST =>
            max(
                0,
                $amount - $availableNonWithdrawableBalance,
            ),

            WalletDebitPolicyEnum::WITHDRAWABLE_FIRST =>
            min(
                $amount,
                $availableWithdrawableBalance,
            ),
        };
    }

    private function withdrawableOnlyAmount(
        int $amount,
        int $availableWithdrawableBalance,
    ): int
    {
        if ($amount > $availableWithdrawableBalance) {
            throw new InsufficientWithdrawableBalanceException(
                'Wallet withdrawable balance is insufficient.'
            );
        }

        return $amount;
    }
}
