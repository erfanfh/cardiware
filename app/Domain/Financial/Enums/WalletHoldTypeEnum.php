<?php

namespace App\Domain\Financial\Enums;

enum WalletHoldTypeEnum: string
{
    /**
     * Wallet contribution to a full or mixed order payment.
     */
    case ORDER_PAYMENT = 'order_payment';

    /**
     * Amount reserved for a withdrawal request.
     */
    case WITHDRAWAL_REQUEST = 'withdrawal_request';

    /**
     * Amount reserved before completing a transfer.
     *
     * Direct transfers may not need this, but the type is available
     * for transfers requiring confirmation or delayed execution.
     */
    case TRANSFER_OUT = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_PAYMENT => 'رزرو پرداخت سفارش',
            self::WITHDRAWAL_REQUEST => 'رزرو درخواست برداشت',
            self::TRANSFER_OUT => 'رزرو انتقال موجودی',
        };
    }

    /**
     * Determine how wallet buckets must be reserved.
     */
    public function debitPolicy(): WalletDebitPolicyEnum
    {
        return match ($this) {
            self::ORDER_PAYMENT =>
            WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,

            self::WITHDRAWAL_REQUEST,
            self::TRANSFER_OUT =>
            WalletDebitPolicyEnum::WITHDRAWABLE_ONLY,
        };
    }

    /**
     * Get transaction type created when this hold is captured.
     */
    public function transactionType(): WalletTransactionTypeEnum
    {
        return match ($this) {
            self::ORDER_PAYMENT =>
            WalletTransactionTypeEnum::ORDER_PAYMENT,

            self::WITHDRAWAL_REQUEST =>
            WalletTransactionTypeEnum::WITHDRAWAL,

            self::TRANSFER_OUT =>
            WalletTransactionTypeEnum::TRANSFER_OUT,
        };
    }
}
