<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

/**
 * تنظیمات صفحه لندینگ جشنواره (یلدا/نوروز/تابستان/بلک‌فرایدی و…) — کاملاً قابل شخصی‌سازی از پنل.
 */
class FestivalSettings extends BaseSettings
{
    protected string $group = 'festival';

    protected array $allowedKeys = [
        'enabled',
        'badge_text',
        'title',
        'subtitle',
        'description',
        'theme_color',
        'countdown_enabled',
        'countdown_ends_at',
        'discount_text',
        'button_text',
        'button_url',
        'coupon_code',
        'coupon_hint',
        'deals_title',
        'deals_items',
        'footer_note',
        'hero_image',
        'bg_image',
        'bg_pattern',
    ];

    protected array $casts = [
        'enabled' => BooleanCast::class,
        'countdown_enabled' => BooleanCast::class,
        'hero_image' => MediaFileCast::class,
        'bg_image' => MediaFileCast::class,
        'deals_items' => RepeaterArrayCast::class,
    ];

    public function isEnabled(): bool
    {
        return (bool) $this->get('enabled', false);
    }

    public function themeColor(): string
    {
        $color = trim((string) $this->get('theme_color', '#e11d48'));

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#e11d48';
    }
}
