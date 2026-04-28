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
        $settingsClass = '\\App\\Infrastructure\\Services\\Setting\\Groups\\' . ucfirst($group) . 'Settings';

        if (!class_exists($settingsClass) || !is_subclass_of($settingsClass, BaseSettings::class)) {
            throw new InvalidArgumentException("Settings group '{$group}' not found or is invalid.");
        }

        $settings = App::make($settingsClass);

        if (!in_array($key, $settings->getAllowedKeys(), true)) {
            throw new InvalidArgumentException("Key '{$key}' is not allowed in group '{$group}'.");
        }

        try {
            $settings->set($key, $value);

            $this->settingCacheInvalidator->afterUpdated($group, $key);

            return true;
        } catch (Throwable $e) {
            Log::info($e->getMessage());

            return false;
        }
    }
}
