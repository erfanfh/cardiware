<?php

namespace App\Application\Shared\Cache\Observers;

use App\Application\Shared\Cache\Invalidators\PublicReadCacheInvalidator;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;

final class PublicReadCacheObserver
{
    public function saved(Model $model): void
    {
        if ($model instanceof User && ! $model->wasRecentlyCreated && ! $model->wasChanged([
            'username', 'display_name', 'first_name', 'last_name', 'avatar_id', 'preset_avatar', 'status', 'deleted_at',
        ])) {
            return;
        }

        app(PublicReadCacheInvalidator::class)->forModel($model);
    }

    public function deleted(Model $model): void
    {
        app(PublicReadCacheInvalidator::class)->forModel($model);
    }
}
