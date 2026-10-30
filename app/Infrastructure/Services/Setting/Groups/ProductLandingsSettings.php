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
        // Lists are read as a whole: deleted rows must never reappear from defaults.
        return $this->get($key, self::defaults()[$key]);
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
