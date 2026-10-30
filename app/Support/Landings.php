<?php

namespace App\Support;

/**
 * ابزارهای موتور لندینگ — دوزبانه‌سازی و آدرس‌ها
 *
 * قرارداد ترجمه: هر آرایه‌ای که دقیقاً دو کلید fa و en داشته باشد
 * یک رشته‌ی ترجمه‌شو است و به locale فعلی resolve می‌شود.
 */
class Landings
{
    /** locale پیش‌فرض سایت */
    public const DEFAULT_LOCALE = 'fa';

    public const LOCALES = ['fa', 'en'];

    /**
     * کل config لندینگ را برای locale فعلی resolve می‌کند؛
     * هر ['fa'=>..,'en'=>..] به رشته‌ی همان زبان تبدیل می‌شود.
     */
    public static function resolve(mixed $value, string $locale): mixed
    {
        if (is_array($value)) {
            if (self::isTranslatable($value)) {
                return $value[$locale] ?? $value[self::DEFAULT_LOCALE];
            }

            return array_map(fn ($item) => self::resolve($item, $locale), $value);
        }

        return $value;
    }

    public static function isTranslatable(array $value): bool
    {
        return isset($value['fa'], $value['en']) && count($value) === 2;
    }

    /** locale فعلی را از مسیر درخواست استخراج می‌کند (/en/... یا پیش‌فرض fa) */
    public static function localeFromRequest(): string
    {
        $segments = request()->segments();

        return ($segments[0] ?? '') === 'en' ? 'en' : self::DEFAULT_LOCALE;
    }

    /** پیشوند زبان برای ساخت URL (fa بدون پیشوند، en با /en) */
    public static function localePrefix(string $locale): string
    {
        return $locale === 'en' ? '/en' : '';
    }


    /* ------------------------------------------------------------------ */
    /*  تنظیمات تم (صفحه /settings) — ذخیره در storage/app/landing-themes   */
    /* ------------------------------------------------------------------ */

    public static function overridesPath(): string
    {
        return storage_path('app/landing-themes.json');
    }

    /** overrideهای ذخیره‌شده همه لندینگ‌ها */
    public static function themeOverrides(?string $theme = null): array
    {
        static $cache = null;
        if ($cache === null) {
            $json = @file_get_contents(self::overridesPath());
            $cache = json_decode((string) $json, true) ?: [];
        }

        if ($theme === null) {
            return $cache;
        }

        return $cache[$theme] ?? [];
    }


    /** لوگوی فعلی یک رقابت — override از /settings یا پیش‌فرض کانفیگ */
    public static function leagueLogo(string $slug, ?string $default = null): ?string
    {
        $override = self::themeOverrides()['catalog_logos'][$slug] ?? null;

        return $override ?: $default;
    }

    public static function saveThemeOverrides(string $theme, array $overrides): void
    {
        $all = self::themeOverrides();
        $all[$theme] = $overrides;
        file_put_contents(self::overridesPath(), json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * URL همین صفحه در زبان دیگر — برای دکمه‌ی تغییر زبان.
     */
    public static function switchUrl(string $to): string
    {
        $path = request()->path() === '/' ? '' : request()->path();

        if (str_starts_with($path, 'en/')) {
            $path = substr($path, 3);
        } elseif ($path === 'en') {
            $path = '';
        }

        return self::localePrefix($to).($path !== '' ? '/'.$path : '') ?: '/';
    }
}
