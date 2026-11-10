<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Services\Setting\BaseSettings;

class VipSettings extends BaseSettings
{
    public const DEFAULT_HERO = [
        'hero_badge' => 'اشتراک ویژه مودینگ‌وی',
        'hero_title' => 'تجربه‌ای ویژه،',
        'hero_highlight' => 'با زمان‌بندی دلخواه شما',
        'hero_description' => 'پلن مناسب خود را انتخاب کنید. پس از پرداخت موفق، اعتبار VIP شما فعال می‌شود و اگر از قبل اشتراک داشته باشید، مدت پلن جدید به انتهای اعتبار فعلی اضافه می‌شود.',
        'hero_button' => 'مشاهده پلن‌ها',
        'hero_benefit_1' => 'پرداخت از مسیر امن فروشگاه',
        'hero_benefit_2' => 'ثبت سفارش در حساب کاربری',
        'hero_benefit_3' => 'تمدید بدون هم‌پوشانی زمان',
    ];

    protected string $group = 'vip';

    protected array $allowedKeys = [
        'hero_badge',
        'hero_title',
        'hero_highlight',
        'hero_description',
        'hero_button',
        'hero_benefit_1',
        'hero_benefit_2',
        'hero_benefit_3',
        'featured_plan_id',
        'purchase_discount_percent',
    ];

    public function hero(): array
    {
        $hero = [];

        foreach (self::DEFAULT_HERO as $key => $default) {
            $hero[$key] = $this->get($key, $default);
        }

        return $hero;
    }

    /**
     * درصد تخفیف عمومی اعضای فعال VIP روی خرید تکی محصولات (۰ = غیرفعال).
     * فقط روی اقلام پولیِ قابلِ خرید اعمال می‌شود؛ پلن‌های اشتراک خودِ VIP و
     * اقلامی که با خود اشتراک باز می‌شوند (vip_access_enabled) شامل نمی‌شوند.
     */
    public function purchaseDiscountPercent(): int
    {
        return max(0, min(90, (int)$this->get('purchase_discount_percent', 0)));
    }
}
