<?php

namespace App\Infrastructure\Services\Setting;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\System\Models\Setting;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LogicException;

abstract class BaseSettings
{
    protected string $group;

    protected array $allowedKeys = [];

    /**
     * Map key name to its Cast class
     *
     * @var array<string, class-string<CastsAttributes>>
     */
    protected array $casts = [];

    /**
     * Per-request in-memory cache
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $resolvedGroups = [];

    public static function clearResolvedGroups(): void
    {
        // Keep the shared cache; only discard values retained by a long-lived worker.
        self::$resolvedGroups = [];
    }

    public function __construct()
    {
        if (empty($this->group) || empty($this->allowedKeys)) {
            throw new LogicException('Subclass must define $group and $allowedKeys.');
        }
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureAllowedKey($key);

        $settings = $this->getAll();
        $rawValue = $settings[$key] ?? $default;

        return $this->castValueForGet($key, $rawValue);
    }

    /**
     * @return array
     */
    protected function getAll(): array
    {
        if (array_key_exists($this->group, self::$resolvedGroups)) {
            return self::$resolvedGroups[$this->group];
        }

        $settings = Cache::rememberForever($this->getCacheKey(), function () {
            return Setting::query()
                ->where('group', $this->group)
                ->pluck('value', 'key')
                ->toArray();
        });

        self::$resolvedGroups[$this->group] = $settings;

        return $settings;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureAllowedKey($key);

        $processedValue = $this->castValueForSet($key, $value);

        Setting::updateOrCreate(
            ['group' => $this->group, 'key' => $key],
            ['value' => $processedValue],
        );

        $this->forgetCache();
    }

    /**
     * @return void
     */
    public function forgetCache(): void
    {
        Cache::forget($this->getCacheKey());

        unset(self::$resolvedGroups[$this->group]);
    }

    /**
     * @return array
     */
    public function getAllowedKeys(): array
    {
        return $this->allowedKeys;
    }

    /**
     * @param string $key
     * @return void
     */
    protected function ensureAllowedKey(string $key): void
    {
        if (!in_array($key, $this->allowedKeys, true)) {
            throw new InvalidArgumentException("Key '{$key}' is not allowed for group '{$this->group}'.");
        }
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return CacheKey::settingsGroup($this->group);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castValueForGet(string $key, mixed $value): mixed
    {
        $castClass = $this->casts[$key] ?? null;

        if (!$castClass) {
            return $value;
        }

        if (!class_exists($castClass) || !is_subclass_of($castClass, CastsAttributes::class)) {
            throw new LogicException("Cast class '{$castClass}' not found or invalid for key '{$key}'.");
        }

        $castInstance = new $castClass();

        return $castInstance->get(app(Setting::class), $key, $value, []);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castValueForSet(string $key, mixed $value): mixed
    {
        $castClass = $this->casts[$key] ?? null;

        if (!$castClass) {
            return $value;
        }

        if (!class_exists($castClass) || !is_subclass_of($castClass, CastsAttributes::class)) {
            throw new LogicException("Cast class '{$castClass}' not found or invalid for key '{$key}'.");
        }

        $castInstance = new $castClass();

        return $castInstance->set(app(Setting::class), $key, $value, []);
    }
}
