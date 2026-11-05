<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class ProductLandingsSettings extends BaseSettings
{
    /** کلید سند لندینگ‌های اضافه‌شده از پنل: p_{key} (مثلاً p_pgl6). */
    public const CUSTOM_KEY_PATTERN = '/^p_[a-z0-9_]{1,40}$/';

    /** نامک لندینگ‌های جدید باید با pgl- شروع شود تا با برگه‌های CMS تداخل نکند. */
    public const CUSTOM_SLUG_PATTERN = '/^pgl-[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** پایه‌هایی که لندینگ جدید از روی آن ساخته می‌شود. */
    public const BASES = ['ultimate' => 'pgl-ultimate', 'fifa23' => 'pgl-fifa-23'];

    protected string $group = 'product_landings';

    protected array $allowedKeys = ['ultimate', 'fifa23', 'pages', 'catalog', 'custom_products'];

    protected array $casts = [
        'ultimate' => ArrayCast::class,
        'fifa23' => ArrayCast::class,
        'pages' => ArrayCast::class,
        'catalog' => ArrayCast::class,
        'custom_products' => ArrayCast::class,
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

    /**
     * لندینگ‌های محصول که از پنل اضافه شده‌اند.
     *
     * @return list<array{key: string, slug: string, label: string, base: string}>
     */
    public function customProducts(): array
    {
        $list = $this->get('custom_products');

        return array_values(array_filter(
            is_array($list) ? $list : [],
            static fn ($item): bool => is_array($item)
                && preg_match(self::CUSTOM_KEY_PATTERN, (string) ($item['key'] ?? '')) === 1
                && preg_match(self::CUSTOM_SLUG_PATTERN, (string) ($item['slug'] ?? '')) === 1
                && isset(self::BASES[$item['base'] ?? '']),
        ));
    }

    /** @return array{key: string, slug: string, label: string, base: string}|null */
    public function customProductByKey(string $key): ?array
    {
        foreach ($this->customProducts() as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
        }

        return null;
    }

    /** همه‌ی سندهای قابل ویرایش (ثابت + اضافه‌شده) با برچسب. */
    public function documentLabels(): array
    {
        $labels = self::labels();
        $custom = [];
        foreach ($this->customProducts() as $item) {
            $custom[$item['key']] = $item['label'];
        }

        // محصولات کنار هم، بعد صفحات و کاتالوگ مشترک.
        return ['ultimate' => $labels['ultimate'], 'fifa23' => $labels['fifa23']]
            + $custom
            + ['pages' => $labels['pages'], 'catalog' => $labels['catalog']];
    }

    /** نامک عمومی لندینگ هر سند محصول (برای صفحات مشترک null). */
    public function slugFor(string $document): ?string
    {
        return self::BASES[$document] ?? $this->customProductByKey($document)['slug'] ?? null;
    }

    public function isDocument(string $key): bool
    {
        return array_key_exists($key, self::defaults()) || $this->customProductByKey($key) !== null;
    }

    /** پیش‌فرض هر سند؛ لندینگ جدید کپی پایه‌ی انتخاب‌شده است (ابتدا غیرفعال). */
    public function documentDefaults(string $key): array
    {
        $builtin = self::defaults();
        if (array_key_exists($key, $builtin)) {
            return (array) $builtin[$key];
        }

        $item = $this->customProductByKey($key);
        if ($item === null) {
            throw new \InvalidArgumentException("Unknown product landing document '{$key}'.");
        }

        $base = (array) $builtin[$item['base']];
        $base['enabled'] = false;
        $base['product_name'] = ['fa' => $item['label'], 'en' => $item['label']];

        return $base;
    }

    public function getAllowedKeys(): array
    {
        return [...$this->allowedKeys, ...array_column($this->customProducts(), 'key')];
    }

    protected function ensureAllowedKey(string $key): void
    {
        if (preg_match(self::CUSTOM_KEY_PATTERN, $key) === 1) {
            return;
        }

        parent::ensureAllowedKey($key);
    }

    protected function castValueForGet(string $key, mixed $value): mixed
    {
        if (preg_match(self::CUSTOM_KEY_PATTERN, $key) === 1) {
            $this->casts[$key] = ArrayCast::class;
        }

        return parent::castValueForGet($key, $value);
    }

    protected function castValueForSet(string $key, mixed $value): mixed
    {
        if (preg_match(self::CUSTOM_KEY_PATTERN, $key) === 1) {
            $this->casts[$key] = ArrayCast::class;
        }

        return parent::castValueForSet($key, $value);
    }

    public function document(string $key): array
    {
        // سندِ ذخیره‌شده روی پیش‌فرض سوار می‌شود: کلیدهای تازه‌ی config
        // (مثل enabled/hidden) خودکار ظاهر می‌شوند؛ اما هر کلید یا فهرستی که
        // کاربر ذخیره کرده عیناً حفظ می‌شود (ردیف‌های حذف‌شده بازنمی‌گردند).
        $defaults = $this->documentDefaults($key);
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
        $key = array_search($slug, self::BASES, true);
        if ($key !== false) {
            return $this->document($key);
        }

        foreach ($this->customProducts() as $item) {
            if ($item['slug'] === $slug) {
                return $this->document($item['key']);
            }
        }

        return config("product-landings.landings.{$slug}");
    }

    public function version(array $document): string
    {
        return hash('sha256', json_encode($document, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }
}
