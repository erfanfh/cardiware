<?php

namespace App\Application\System\Actions;

use App\Application\Shared\Cache\Invalidators\SettingCacheInvalidator;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class UpdateSettingAction
{
    public function __construct(
        protected SettingCacheInvalidator $settingCacheInvalidator,
    )
    {
    }

    /**
     * Updates the given Setting.
     *
     * @param string $group
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws BindingResolutionException
     */
    public function execute(string $group, string $key, mixed $value): bool
    {
        $settings = $this->resolveSettings($group, $key);

        try {
            $this->persist($settings, $group, $key, $value);

            return true;
        } catch (Throwable $e) {
            // علت واقعی خطا باید در لاگ قابل پیدا کردن باشد (قبلاً info بی‌جزئیات بود)
            Log::error('Setting persist failed', [
                'group' => $group,
                'key' => $key,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'at' => $e->getFile().':'.$e->getLine(),
            ]);

            return false;
        }
    }

    /** Persist within the caller's transaction without discarding the original failure. */
    public function executeOrFail(string $group, string $key, mixed $value): void
    {
        $this->persist($this->resolveSettings($group, $key), $group, $key, $value);
    }

    private function resolveSettings(string $group, string $key): BaseSettings
    {
        $settingsClass = '\\App\\Infrastructure\\Services\\Setting\\Groups\\' . ucfirst($group) . 'Settings';

        if (!class_exists($settingsClass) || !is_subclass_of($settingsClass, BaseSettings::class)) {
            throw new InvalidArgumentException("Settings group '{$group}' not found or is invalid.");
        }

        $settings = App::make($settingsClass);

        if (!in_array($key, $settings->getAllowedKeys(), true)) {
            throw new InvalidArgumentException("Key '{$key}' is not allowed in group '{$group}'.");
        }

        return $settings;
    }

    private function persist(BaseSettings $settings, string $group, string $key, mixed $value): void
    {
        $settings->set($key, $value);
        $this->settingCacheInvalidator->afterUpdated($group, $key);
    }
}
