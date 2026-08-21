<?php

namespace App\Domain\Financial\Enums;

enum WalletTransactionTypeEnum: string
{
    /**
     * Wallet recharge through an external payment gateway.
     */
    case TOP_UP = 'top_up';

    /**
     * Full or partial order payment using wallet balance.
     */
    case ORDER_PAYMENT = 'order_payment';

    /**
     * Returning an order payment to the wallet.
     */
    case ORDER_REFUND = 'order_refund';

    /**
     * Incoming transfer from another user.
     */
    case TRANSFER_IN = 'transfer_in';

    /**
     * Outgoing transfer to another user.
     */
    case TRANSFER_OUT = 'transfer_out';

    /**
     * Completed withdrawal request.
     */
    case WITHDRAWAL = 'withdrawal';

    /**
     * Manual credit applied by an administrator.
     */
    case ADMIN_CREDIT = 'admin_credit';

    /**
     * Manual debit applied by an administrator.
     */
    case ADMIN_DEBIT = 'admin_debit';

    /**
     * Promotional or gifted credit.
     */
    case BONUS = 'bonus';

    /**
     * Non-withdrawable credit created by converting loyalty points.
     */
    case LOYALTY_CONVERSION = 'loyalty_conversion';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::TOP_UP => 'شارژ کیف پول',
            self::ORDER_PAYMENT => 'پرداخت سفارش',
            self::ORDER_REFUND => 'بازگشت مبلغ سفارش',
            self::TRANSFER_IN => 'دریافت انتقال',
            self::TRANSFER_OUT => 'انتقال موجودی',
            self::WITHDRAWAL => 'برداشت وجه',
            self::ADMIN_CREDIT => 'افزایش موجودی توسط مدیر',
            self::ADMIN_DEBIT => 'کاهش موجودی توسط مدیر',
            self::BONUS => 'اعتبار هدیه',
            self::LOYALTY_CONVERSION => 'تبدیل امتیاز باشگاه',
        };
    }

    /**
     * Get the expected transaction direction.
     */
    public function direction(): WalletTransactionDirectionEnum
    {
        return match ($this) {
            self::TOP_UP,
            self::ORDER_REFUND,
            self::TRANSFER_IN,
            self::ADMIN_CREDIT,
            self::BONUS => WalletTransactionDirectionEnum::CREDIT,
            self::LOYALTY_CONVERSION => WalletTransactionDirectionEnum::CREDIT,

            self::ORDER_PAYMENT,
            self::TRANSFER_OUT,
            self::WITHDRAWAL,
            self::ADMIN_DEBIT => WalletTransactionDirectionEnum::DEBIT,
        };
    }
}
