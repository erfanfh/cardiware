<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Domain\Financial\Models\Wallet;
use InvalidArgumentException;

/**
 * سقف مبلغ قابل‌پرداخت از کیف پول برای یک سفارش.
 *
 * موجودی کیف پول دو منشأ دارد: پول واقعی (شارژ/برداشت‌پذیر) و اعتبار باشگاه
 * (غیربرداشت‌پذیر، حاصل تبدیل امتیاز). سهم اعتبار باشگاه از پرداخت هر سفارش
 * به Setting «loyalty.max_credit_percent_per_order» محدود می‌شود (پیش‌فرض ۲۰٪،
 * صفر یا ۱۰۰ = بدون محدودیت) تا کش‌بک امتیاز کل سفارش را رایگان نکند.
 */
final class ResolveWalletOrderPaymentCapAction
{
    public function __construct(
        private readonly WalletBalanceCalculator $calculator,
    ) {
    }

    public function execute(int $orderTotal, Wallet $wallet): int
    {
        if ($orderTotal < 0) {
            throw new InvalidArgumentException('Order total cannot be negative.');
        }

        $available = $this->calculator->availableBalance($wallet);

        if ($orderTotal === 0) {
            return 0;
        }

        // پیش‌فرض ۱۰۰ = بدون محدودیت تا وقتی ادمین صریحاً سقف تعیین کند؛
        // دموی آفلاین این مقدار را روی ۲۰ تنظیم می‌کند.
        $percent = (int) get_setting('loyalty', 'max_credit_percent_per_order', 100);

        if ($percent <= 0 || $percent >= 100) {
            return $available;
        }

        $credit = $this->calculator->availableNonWithdrawableBalance($wallet);
        $general = max(0, $available - $credit);

        $maxCreditShare = intdiv($orderTotal * $percent, 100);

        return min($available, $general + min($credit, $maxCreditShare));
    }
}
