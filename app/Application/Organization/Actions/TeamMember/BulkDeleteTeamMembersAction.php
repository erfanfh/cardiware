<?php

namespace App\Application\Organization\Actions\TeamMember;

use App\Application\Shared\Cache\Invalidators\ProductCacheInvalidator;
use App\Domain\Organization\Models\TeamMember;

class BulkDeleteTeamMembersAction
{
    public function __construct(
        protected ProductCacheInvalidator $productCacheInvalidator,
    )
    {
    }

    /**
     * @param array<int, int> $ids
     */
    public function execute(array $ids): int
    {
        $members = TeamMember::query()
            ->whereKey($ids)
            ->get();
        $members->each->delete();

        if ($members->isNotEmpty()) {
            $this->productCacheInvalidator->flushProductDetails();
        }

        return $members->count();
    }
}
