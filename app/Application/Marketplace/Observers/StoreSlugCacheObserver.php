<?php

namespace App\Application\Marketplace\Observers;

use App\Application\Shared\Cache\Invalidators\StoreSlugCacheInvalidator;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSlug;

final class StoreSlugCacheObserver
{
    public function __construct(
        private readonly StoreSlugCacheInvalidator $invalidator,
    ) {}

    public function saved(Store|StoreSlug $model): void
    {
        $this->forget($model);
    }

    public function deleted(Store|StoreSlug $model): void
    {
        $this->forget($model);
    }

    public function restored(Store $store): void
    {
        $this->forget($store);
    }

    private function forget(Store|StoreSlug $model): void
    {
        if ($model instanceof Store) {
            $model->getConnection()->afterCommit(
                fn () => $this->invalidator->forgetForModel($model)
            );

            return;
        }

        // Capture both keys before Eloquent synchronizes the original attributes.
        $previousSlug = (string) $model->getRawOriginal('route_key');
        $currentSlug = $model->route_key;

        $model->getConnection()->afterCommit(function () use ($previousSlug, $currentSlug): void {
            $this->invalidator->forgetBySlug($previousSlug);
            $this->invalidator->forgetBySlug($currentSlug);
        });
    }
}
