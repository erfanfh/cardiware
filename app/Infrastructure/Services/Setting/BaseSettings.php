<?php

namespace App\Infrastructure\Services\Setting;

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
     * @var array
     */
    protected array $casts = [];

    public function __construct()
    {
        if (empty($this->group) || empty($this->allowedKeys))
            throw new LogicException("Subclass must define \$group and \$allowedKeys.");
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!in_array($key, $this->allowedKeys))
            throw new InvalidArgumentException("Key '$key' is not allowed for group '$this->group'.");

        $settings = $this->getAll();
        $rawValue = $settings[$key] ?? $default;

        if (isset($this->casts[$key])) {
            $castClass = $this->casts[$key];
            if (!class_exists($castClass) || !is_subclass_of($castClass, CastsAttributes::class))
                throw new LogicException("Cast class '$castClass' not found or invalid for key '$key'.");

            $castInstance = new $castClass();
            return $castInstance->get(app(Setting::class), $key, $rawValue, []);
        }

        return $rawValue;
    }

    /**
     * @return array
     */
    protected function getAll(): array
    {
        return Cache::rememberForever("settings_$this->group", function () {
            return Setting::where('group', $this->group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        if (!in_array($key, $this->allowedKeys))
            throw new InvalidArgumentException("Key '$key' is not allowed for group '$this->group'.");

        $processedValue = $value;

        if (isset($this->casts[$key])) {
            $castClass = $this->casts[$key];
            if (!class_exists($castClass) || !is_subclass_of($castClass, CastsAttributes::class)) {
                throw new LogicException("Cast class '$castClass' not found or invalid for key '$key'.");
            }
            $castInstance = new $castClass();
            $processedValue = $castInstance->set(app(Setting::class), $key, $value, []);
        }

        Setting::updateOrCreate(
            ['group' => $this->group, 'key' => $key],
            ['value' => $processedValue],
        );

        Cache::forget("settings_$this->group");
    }

    /**
     * @return array
     */
    public function getAllowedKeys(): array
    {
        return $this->allowedKeys;
    }
}

