<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\Invalidators\SeoRedirectCacheInvalidator;
use App\Domain\Seo\Models\Redirect;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteSelectedRedirectsAction
{
    public function __construct(
        private readonly SeoRedirectCacheInvalidator $cacheInvalidator,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(array $ids): int
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

        $deletedCount = DB::transaction(
            fn (): int => Redirect::query()
                ->whereKey($ids)
                ->delete()
        );

        if ($deletedCount > 0) {
            $this->cacheInvalidator->invalidate();
        }

        return $deletedCount;
    }
}
