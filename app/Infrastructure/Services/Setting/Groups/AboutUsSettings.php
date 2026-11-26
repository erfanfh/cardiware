<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class AboutUsSettings extends BaseSettings
{
    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'داستان Cardiware',
            'hero_title' => 'از علاقه به بازی تا ساخت تجربه‌ای بهتر برای گیمرها',
            'hero_description' => 'Cardiware خانه‌ای برای پروژه‌های تخصصی، محصولات مطمئن و آموزش‌های کاربردی دنیای گیم است؛ جایی که کیفیت، شفافیت و پشتیبانی واقعی در اولویت قرار دارد.',
            'hero_primary_button_title' => 'مشاهده پروژه‌ها',
            'hero_secondary_button_title' => 'ارتباط با ما',
            'hero_image' => null,
            'stats_in_progress_title' => 'پروژه در حال توسعه',
            'stats_completed_title' => 'پروژه تکمیل‌شده',
            'stats_products_title' => 'محصول منتشرشده',
            'stats_users_title' => 'عضو جامعه Cardiware',
            'our_mission_box_title' => 'ماموریت ما',
            'our_mission_description' => 'ماموریت ما ساخت و عرضه محتوایی است که تجربه بازی را دقیق‌تر، جذاب‌تر و قابل اعتمادتر کند. هر پروژه با توجه به بازخورد کاربران توسعه پیدا می‌کند و تا رسیدن به نسخه‌ای پایدار همراه آن می‌مانیم.',
            'what_we_build_box_title' => 'در Cardiware چه می‌سازیم؟',
            'what_we_build_items' => [
                [
                    'title' => 'پروژه‌ها و پچ‌های تخصصی',
                    'subtitle' => 'پروژه‌هایی با نقشه راه شفاف و گزارش پیشرفت واقعی.',
                    'url' => '/projects',
                ],
                [
                    'title' => 'محصولات و ابزارهای گیمینگ',
                    'subtitle' => 'محصولات دیجیتال و خدماتی با اطلاعات کامل و پشتیبانی.',
                    'url' => '/products',
                ],
                [
                    'title' => 'آموزش‌ها و راهنماها',
                    'subtitle' => 'مقاله‌های کاربردی برای نصب، اجرا و رفع مشکلات رایج.',
                    'url' => '/posts',
                ],
                [
                    'title' => 'ویدیوهای تخصصی',
                    'subtitle' => 'آموزش‌ها، معرفی پروژه‌ها و تازه‌های دنیای مادینگ.',
                    'url' => '/videos',
                ],
            ],
            'projects_box_title' => 'آخرین پروژه‌های Cardiware',
            'projects_box_description' => 'وضعیت توسعه و آخرین پیشرفت پروژه‌های فعال را شفاف دنبال کنید.',
            'projects_button_title' => 'مشاهده همه پروژه‌ها',
            'projects_count' => 3,
            'our_team_box_title' => 'اعضای تیم Cardiware',
            'our_team_description' => 'افرادی که پشت طراحی، توسعه، انتشار و پشتیبانی پروژه‌های Cardiware هستند.',
            'team_members_count' => 6,
            'faqs_box_title' => 'پرسش‌های متداول',
            'faqs_description' => 'پاسخ کوتاه به سوال‌هایی که بیشتر از ما پرسیده می‌شود.',
            'faqs_items' => [
                [
                    'title' => 'چطور وضعیت پروژه‌ها را دنبال کنم؟',
                    'answer' => 'در صفحه هر پروژه، درصد پیشرفت، آخرین بروزرسانی و جزئیات نسخه‌ها به‌صورت شفاف نمایش داده می‌شود.',
                ],
                [
                    'title' => 'برای دریافت راهنمایی یا پشتیبانی چه کار کنم؟',
                    'answer' => 'پس از ورود به حساب کاربری، از بخش تیکت‌ها درخواست خود را ثبت کنید تا گفتگو و پیگیری آن در یک مسیر مشخص انجام شود.',
                ],
                [
                    'title' => 'محصولات و فایل‌های خریداری‌شده از کجا در دسترس هستند؟',
                    'answer' => 'پس از تکمیل خرید، دسترسی‌های شما در حساب کاربری و بخش دانلودها قرار می‌گیرد.',
                ],
            ],
            'contact_us_box_title' => 'برای گفتگو و پشتیبانی کنار شما هستیم',
            'contact_us_description' => 'برای پرسش‌های فنی، پیگیری خرید یا ارائه پیشنهاد، از سیستم تیکت استفاده کنید تا درخواست شما ثبت شود و پاسخ را از همان مسیر دنبال کنید.',
            'contact_us_button_title' => 'ورود به مرکز پشتیبانی',
            'contact_box_title' => 'راه‌های ارتباطی Cardiware',
            'contact_telegram' => '',
            'contact_instagram' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_hour' => '',
        ];
    }

    protected string $group = 'about_us';

    protected array $allowedKeys = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'hero_primary_button_title',
        'hero_secondary_button_title',
        'hero_image',
        'stats_in_progress_title',
        'stats_completed_title',
        'stats_products_title',
        'stats_users_title',
        'our_mission_box_title',
        'our_mission_description',
        'what_we_build_box_title',
        'what_we_build_items',
        'projects_box_title',
        'projects_box_description',
        'projects_button_title',
        'projects_count',
        'our_team_box_title',
        'our_team_description',
        'team_members_count',
        'faqs_box_title',
        'faqs_description',
        'faqs_items',
        'contact_us_box_title',
        'contact_us_description',
        'contact_us_button_title',
        'contact_box_title',
        'contact_telegram',
        'contact_instagram',
        'contact_email',
        'contact_phone',
        'contact_hour',
    ];

    protected array $casts = [
        'hero_image' => MediaFileCast::class,
        'what_we_build_items' => RepeaterArrayCast::class,
        'projects_count' => IntegerCast::class,
        'team_members_count' => IntegerCast::class,
        'faqs_items' => RepeaterArrayCast::class,
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
