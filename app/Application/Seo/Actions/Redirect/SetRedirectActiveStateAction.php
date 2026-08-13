<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\Invalidators\SeoRedirectCacheInvalidator;
use App\Domain\Seo\Models\Redirect;

class SetRedirectActiveStateAction
{
    public function __construct(
        private readonly SeoRedirectCacheInvalidator $cacheInvalidator,
    ) {
    }

    public function execute(array $ids, bool $isActive): int
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return 0;
        }

        $updatedCount = Redirect::query()
            ->whereKey($ids)
            ->where('is_active', !$isActive)
            ->update([
                'is_active' => $isActive,
                'updated_at' => now(),
            ]);

        if ($updatedCount > 0) {
            $this->cacheInvalidator->invalidate();
        }

        return $updatedCount;
    }
}
