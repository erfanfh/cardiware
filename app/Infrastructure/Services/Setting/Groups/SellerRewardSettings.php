<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

/**
 * پاداش برترین فروشنده‌های هفته/ماه/سال.
 *  - commission_bonus_bp: سهم اضافه‌ی فروشنده از هر فروش (۲۰۰ = ۲٪ کمیسیون کمتر برای سایت)
 *  - bonus_days: چند روز بعد از اعلام برندگان این سهم اضافه فعال است
 *  - coupon_*: کد تخفیف شخصی برای اعضای فروشگاه (خرید سایر محصولات)
 *  - spotlight_days: چند روز محصولات فروشگاه در «فروشگاه ویژه» صفحه اصلی نمایش داده شوند
 */
class SellerRewardSettings extends BaseSettings
{
    protected string $group = 'seller_rewards';

    protected array $allowedKeys = ['enabled', 'prizes', 'min_net_sales', 'enabled_since', 'spotlight_title'];

    protected array $casts = [
        'enabled' => BooleanCast::class,
        'prizes' => ArrayCast::class,
        'min_net_sales' => ArrayCast::class,
    ];

    public const PERIODS = ['week' => 'هفته', 'month' => 'ماه', 'year' => 'سال'];

    public const DEFAULT_PRIZES = [
        'week' => [
            ['commission_bonus_bp' => 200, 'bonus_days' => 7, 'coupon_percent' => 0, 'coupon_max_amount' => 0, 'coupon_days' => 30, 'spotlight_days' => 7],
            ['commission_bonus_bp' => 100, 'bonus_days' => 7, 'coupon_percent' => 0, 'coupon_max_amount' => 0, 'coupon_days' => 30, 'spotlight_days' => 0],
            ['commission_bonus_bp' => 50, 'bonus_days' => 7, 'coupon_percent' => 0, 'coupon_max_amount' => 0, 'coupon_days' => 30, 'spotlight_days' => 0],
        ],
        'month' => [
            ['commission_bonus_bp' => 300, 'bonus_days' => 30, 'coupon_percent' => 20, 'coupon_max_amount' => 300000, 'coupon_days' => 30, 'spotlight_days' => 7],
            ['commission_bonus_bp' => 200, 'bonus_days' => 30, 'coupon_percent' => 15, 'coupon_max_amount' => 200000, 'coupon_days' => 30, 'spotlight_days' => 0],
            ['commission_bonus_bp' => 100, 'bonus_days' => 30, 'coupon_percent' => 10, 'coupon_max_amount' => 100000, 'coupon_days' => 30, 'spotlight_days' => 0],
        ],
        'year' => [
            ['commission_bonus_bp' => 500, 'bonus_days' => 90, 'coupon_percent' => 50, 'coupon_max_amount' => 1000000, 'coupon_days' => 90, 'spotlight_days' => 30],
            ['commission_bonus_bp' => 300, 'bonus_days' => 90, 'coupon_percent' => 30, 'coupon_max_amount' => 500000, 'coupon_days' => 90, 'spotlight_days' => 14],
            ['commission_bonus_bp' => 200, 'bonus_days' => 90, 'coupon_percent' => 20, 'coupon_max_amount' => 300000, 'coupon_days' => 90, 'spotlight_days' => 7],
        ],
    ];

    /** حداقل فروش خالص (تومان) برای ورود به رقابت هر دوره؛ جلوی بردن با فروش ناچیز را می‌گیرد. */
    public const DEFAULT_MIN_NET_SALES = ['week' => 500000, 'month' => 2000000, 'year' => 20000000];

    public function enabled(): bool
    {
        return (bool) $this->get('enabled', true);
    }

    /** @return list<array<string, int>> */
    public function prizes(string $periodType): array
    {
        $configured = $this->get('prizes');
        $rows = is_array($configured) && is_array($configured[$periodType] ?? null)
            ? array_values($configured[$periodType])
            : self::DEFAULT_PRIZES[$periodType] ?? [];

        return array_map(static fn($row): array => [
            'commission_bonus_bp' => max(0, min(5000, (int) ($row['commission_bonus_bp'] ?? 0))),
            'bonus_days' => max(0, (int) ($row['bonus_days'] ?? 0)),
            'coupon_percent' => max(0, min(100, (int) ($row['coupon_percent'] ?? 0))),
            'coupon_max_amount' => max(0, (int) ($row['coupon_max_amount'] ?? 0)),
            'coupon_days' => max(1, (int) ($row['coupon_days'] ?? 30)),
            'spotlight_days' => max(0, (int) ($row['spotlight_days'] ?? 0)),
        ], array_slice($rows, 0, 3));
    }

    public function minNetSales(string $periodType): int
    {
        $configured = $this->get('min_net_sales');

        return max(0, (int) (is_array($configured) && isset($configured[$periodType])
            ? $configured[$periodType]
            : self::DEFAULT_MIN_NET_SALES[$periodType] ?? 0));
    }
}
