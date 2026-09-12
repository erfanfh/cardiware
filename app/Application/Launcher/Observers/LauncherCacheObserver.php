<?php

namespace App\Application\Launcher\Observers;

use App\Application\Shared\Cache\Invalidators\LauncherCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherGuide;
use App\Domain\Launcher\Models\LauncherNotification;
use App\Domain\Launcher\Models\LauncherSlide;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

/**
 * One observer for every model whose change must invalidate launcher API cache entries
 * that are NOT already covered by a manifest-revision bump. Registered per model in
 * AppServiceProvider; the model type decides which tags are flushed.
 */
final class LauncherCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly LauncherCacheInvalidator $invalidator,
    ) {}

    public function created(Model $model): void
    {
        $this->flush($model);
    }

    public function updated(Model $model): void
    {
        $this->flush($model);
    }

    public function deleted(Model $model): void
    {
        $this->flush($model);
    }

    public function restored(Model $model): void
    {
        $this->flush($model);
    }

    private function flush(Model $model): void
    {
        match (true) {
            $model instanceof LauncherSlide,
            $model instanceof LauncherNotification,
            $model instanceof LauncherGuide => $this->invalidator->flushPortal(),

            // Catalog visibility depends on product status/title; library rows embed the
            // product summary.
            $model instanceof Product => $this->flushProduct($model),

            $model instanceof PurchaseAccess => $this->invalidator->flushLibraryForUser(
                (int) $model->user_id
            ),

            $model instanceof LauncherDevice => $this->invalidator->flushDevice(
                (int) $model->getKey()
            ),

            default => null,
        };
    }

    private function flushProduct(Product $product): void
    {
        if (! $product->wasRecentlyCreated && ! $product->wasChanged(['status', 'title', 'slug', 'picture_id'])
            && $product->exists) {
            return;
        }
        $this->invalidator->flushCatalog();
        $this->invalidator->flushLibraryAll();
    }
}
