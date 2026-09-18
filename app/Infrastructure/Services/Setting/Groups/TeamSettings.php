<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class TeamSettings extends BaseSettings
{
    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'تیم اصلی مودینگ‌وی',
            'hero_title' => 'آدم‌هایی که پشت تجربه مودینگ‌وی هستند',
            'hero_description' => 'از توسعه پچ و طراحی محصول تا تولید محتوا و پشتیبانی؛ هر خروجی مودینگ‌وی نتیجه همکاری یک تیم تخصصی و متعهد است.',
            'hero_primary_button_title' => 'آشنایی با اعضا',
            'hero_secondary_button_title' => 'همکاری با ما',
            'stats_members_title' => 'عضو منتخب',
            'stats_since_title' => 'سال شروع',
            'stats_since_year' => 1396,
            'stats_products_title' => 'محصول منتشرشده',
            'stats_content_title' => 'محتوای آموزشی',
            'highlights_title' => 'چطور در کنار هم کار می‌کنیم؟',
            'highlights_description' => 'ترکیبی از تخصص‌های مکمل با یک هدف مشترک: ساخت تجربه‌ای مطمئن‌تر برای گیمرها.',
            'highlights_items' => [
                [
                    'title' => 'تخصص‌های مکمل',
                    'description' => 'هر عضو روی حوزه‌ای مشخص تمرکز دارد و دانش خود را در اختیار کل تیم می‌گذارد.',
                ],
                [
                    'title' => 'همکاری شفاف',
                    'description' => 'برنامه‌ریزی، توسعه و بازبینی در یک جریان مشترک انجام می‌شود تا چیزی از قلم نیفتد.',
                ],
                [
                    'title' => 'تعهد به کیفیت',
                    'description' => 'پیش از انتشار، خروجی‌ها از نظر نصب، اجرا و تجربه کاربری با دقت بررسی می‌شوند.',
                ],
            ],
            'members_title' => 'اعضای تیم مودینگ‌وی',
            'members_description' => 'با کسانی آشنا شوید که هر روز برای توسعه، محتوا و پشتیبانی مودینگ‌وی تلاش می‌کنند.',
            'members_empty_title' => 'هنوز عضوی برای نمایش در این صفحه انتخاب نشده است.',
            'member_ids' => [],
            'process_title' => 'مسیر ساخت یک خروجی باکیفیت',
            'process_description' => 'هر پروژه از یک مسیر مشخص عبور می‌کند تا نتیجه نهایی پایدار و قابل اتکا باشد.',
            'process_items' => [
                [
                    'title' => 'ایده و برنامه‌ریزی',
                    'description' => 'نیاز کاربران بررسی و مسئولیت هر بخش مشخص می‌شود.',
                ],
                [
                    'title' => 'توسعه و طراحی',
                    'description' => 'فایل‌ها، رابط‌ها و محتوای مورد نیاز توسط اعضای تخصصی آماده می‌شوند.',
                ],
                [
                    'title' => 'تست و بازبینی',
                    'description' => 'خروجی از نظر نصب، اجرا، کیفیت و تجربه کاربر دوباره بررسی می‌شود.',
                ],
                [
                    'title' => 'انتشار و پشتیبانی',
                    'description' => 'نسخه نهایی منتشر می‌شود و تیم در ادامه پاسخ‌گوی کاربران می‌ماند.',
                ],
            ],
            'cta_badge' => 'فرصت همکاری',
            'cta_title' => 'می‌خواهی بخشی از مسیر مودینگ‌وی باشی؟',
            'cta_description' => 'اگر در توسعه، تولید محتوا، طراحی یا پشتیبانی تجربه داری، خوشحال می‌شویم بیشتر با تو آشنا شویم.',
            'cta_primary_button_title' => 'ارسال درخواست همکاری',
            'cta_primary_button_url' => '',
            'cta_secondary_button_title' => 'مشاهده موقعیت‌ها',
            'cta_secondary_button_url' => '',
        ];
    }

    protected string $group = 'team';

    protected array $allowedKeys = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'hero_primary_button_title',
        'hero_secondary_button_title',
        'stats_members_title',
        'stats_since_title',
        'stats_since_year',
        'stats_products_title',
        'stats_content_title',
        'highlights_title',
        'highlights_description',
        'highlights_items',
        'members_title',
        'members_description',
        'members_empty_title',
        'member_ids',
        'process_title',
        'process_description',
        'process_items',
        'cta_badge',
        'cta_title',
        'cta_description',
        'cta_primary_button_title',
        'cta_primary_button_url',
        'cta_secondary_button_title',
        'cta_secondary_button_url',
    ];

    protected array $casts = [
        'stats_since_year' => IntegerCast::class,
        'highlights_items' => RepeaterArrayCast::class,
        'member_ids' => ArrayCast::class,
        'process_items' => RepeaterArrayCast::class,
    ];

    /** @return array<string, mixed> */
    public function values(): array
    {
        $defaults = self::defaults();
        $values = [];

        foreach ($this->allowedKeys as $key) {
            $values[$key] = $this->get($key, $defaults[$key] ?? null);
        }

        return $values;
    }
}
