<?php

namespace App\Application\Catalog\Observers;

use App\Application\Shared\Cache\Invalidators\ProductSlugCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSlug;

final class ProductSlugCacheObserver
{
    public function __construct(
        private readonly ProductSlugCacheInvalidator $invalidator,
    ) {}

    public function saved(Product|ProductSlug $model): void
    {
        $this->forget($model);
    }

    public function deleted(Product|ProductSlug $model): void
    {
        $this->forget($model);
    }

    public function restored(Product $product): void
    {
        $this->forget($product);
    }

    private function forget(Product|ProductSlug $model): void
    {
        if ($model instanceof Product) {
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
