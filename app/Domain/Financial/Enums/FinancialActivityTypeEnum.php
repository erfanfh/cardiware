<?php

namespace App\Domain\Financial\Enums;

enum FinancialActivityTypeEnum: string
{
    /**
     * Direct, wallet or mixed order payment.
     */
    case ORDER_PAYMENT = 'order_payment';

    /**
     * Normal wallet top-up.
     */
    case WALLET_TOP_UP = 'wallet_top_up';

    /**
     * Wallet balance transfer between users.
     */
    case WALLET_TRANSFER = 'wallet_transfer';

    /**
     * Completed payout from withdrawable wallet balance.
     */
    case WITHDRAWAL = 'withdrawal';

    /**
     * Returning an order payment to the wallet.
     */
    case ORDER_REFUND = 'order_refund';

    /**
     * Wallet credit created by an administrator.
     */
    case ADMIN_CREDIT = 'admin_credit';

    /**
     * Wallet debit created by an administrator.
     */
    case ADMIN_DEBIT = 'admin_debit';

    /**
     * Promotional or gifted wallet credit.
     */
    case BONUS = 'bonus';

    /**
     * Wallet credit created from loyalty points.
     */
    case LOYALTY_CONVERSION = 'loyalty_conversion';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_PAYMENT => 'پرداخت سفارش',
            self::WALLET_TOP_UP => 'شارژ کیف پول',
            self::WALLET_TRANSFER => 'انتقال موجودی',
            self::WITHDRAWAL => 'برداشت وجه',
            self::ORDER_REFUND => 'بازگشت مبلغ سفارش',
            self::ADMIN_CREDIT => 'افزایش موجودی توسط مدیر',
            self::ADMIN_DEBIT => 'کاهش موجودی توسط مدیر',
            self::BONUS => 'اعتبار هدیه',
            self::LOYALTY_CONVERSION => 'تبدیل امتیاز باشگاه',
        };
    }
}
