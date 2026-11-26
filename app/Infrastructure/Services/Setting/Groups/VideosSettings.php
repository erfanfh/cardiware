<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class VideosSettings extends BaseSettings
{
    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'Cardiware تی‌وی',
            'hero_title' => 'مرجع ویدیوهای آموزشی، معرفی پچ‌ها و تازه‌های دنیای گیم',
            'hero_description' => 'جدیدترین آموزش‌های مادینگ، معرفی پچ‌ها، تریلرها، بررسی‌ها و ویدیوهای تخصصی بازی را یک‌جا تماشا کنید.',
            'hero_primary_button_title' => 'مشاهده ویدیوها',
            'hero_secondary_button_title' => 'محبوب‌ترین ویدیوها',
            'featured_badge' => 'پیشنهاد ویژه',
            'archive_title' => 'آرشیو ویدیوها',
            'archive_description' => 'آخرین آموزش‌ها، معرفی‌ها و ویدیوهای جدید Cardiware',
            'trending_title' => 'ویدیوهای ترند',
            'categories_title' => 'دسته‌بندی‌های ویدیو',
            'follow_title' => 'ویدیو جدیدی منتشر شد؟',
            'follow_description' => 'آخرین آموزش‌ها و ویدیوهای معرفی پچ‌ها را از دست ندهید و همیشه به‌روز بمانید.',
            'follow_button_title' => 'دنبال کردن کانال',
            'follow_button_url' => '',
            'empty_title' => 'ویدیویی پیدا نشد',
            'empty_description' => 'در حال حاضر ویدیویی متناسب با این بخش وجود ندارد.',
        ];
    }

    protected string $group = 'videos';

    protected array $allowedKeys = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'hero_primary_button_title',
        'hero_secondary_button_title',
        'hero_image',
        'featured_badge',
        'archive_title',
        'archive_description',
        'trending_title',
        'categories_title',
        'follow_title',
        'follow_description',
        'follow_button_title',
        'follow_button_url',
        'empty_title',
        'empty_description',
    ];

    protected array $casts = [
        'hero_image' => MediaFileCast::class,
    ];
}
