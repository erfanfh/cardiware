<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class ProductLandingsSettings extends BaseSettings
{
    protected string $group = 'product_landings';

    protected array $allowedKeys = ['ultimate', 'fifa23', 'pages', 'catalog'];

    protected array $casts = [
        'ultimate' => ArrayCast::class,
        'fifa23' => ArrayCast::class,
        'pages' => ArrayCast::class,
        'catalog' => ArrayCast::class,
    ];

    public static function defaults(): array
    {
        return [
            'ultimate' => config('product-landings.landings.pgl-ultimate'),
            'fifa23' => config('product-landings.landings.pgl-fifa-23'),
            'pages' => config('product-landings.pages'),
            'catalog' => config('landing-catalog'),
        ];
    }

    public static function labels(): array
    {
        return [
            'ultimate' => 'PGL Ultimate 27',
            'fifa23' => 'PGL FIFA 23',
            'pages' => 'صفحات جزئیات مشترک',
            'catalog' => 'کاتالوگ لیگ‌ها، تیم‌ها و جام‌ها',
        ];
    }

    public function document(string $key): array
    {
        // سندِ ذخیره‌شده روی پیش‌فرضِ config سوار می‌شود: کلیدهای تازه‌ی config
        // (مثل enabled/hidden) خودکار ظاهر می‌شوند؛ اما هر کلید یا فهرستی که
        // کاربر ذخیره کرده عیناً حفظ می‌شود (ردیف‌های حذف‌شده بازنمی‌گردند).
        $defaults = self::defaults()[$key];
        $raw = $this->getAll()[$key] ?? null;
        $saved = $raw === null ? [] : (array) $this->castValueForGet($key, $raw);

        return self::mergeDefaults($defaults, $saved);
    }

    /** ادغام امن: فقط نگاشت‌های انجمنی بازتوزیع می‌شوند؛ فهرست‌ها کامل جایگزین. */
    private static function mergeDefaults(array $defaults, array $saved): array
    {
        $isMap = static fn (array $a): bool => !array_is_list($a);

        foreach ($saved as $key => $value) {
            if (isset($defaults[$key]) && is_array($defaults[$key]) && is_array($value)
                && $isMap($defaults[$key]) && $isMap($value)) {
                $defaults[$key] = self::mergeDefaults($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    public function product(string $slug): ?array
    {
        $key = match ($slug) {
            'pgl-ultimate' => 'ultimate',
            'pgl-fifa-23' => 'fifa23',
            default => null,
        };

        return $key === null ? config("product-landings.landings.{$slug}") : $this->document($key);
    }

    public function version(array $document): string
    {
        return hash('sha256', json_encode($document, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }
}
