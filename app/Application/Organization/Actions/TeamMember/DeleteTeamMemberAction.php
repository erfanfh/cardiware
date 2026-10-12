<?php

namespace App\Application\Organization\Actions\TeamMember;

use App\Application\Shared\Cache\Invalidators\ProductCacheInvalidator;
use App\Domain\Organization\Models\TeamMember;

class DeleteTeamMemberAction
{
    public function __construct(
        protected ProductCacheInvalidator $productCacheInvalidator,
    )
    {
    }

    public function execute(TeamMember $teamMember): void
    {
        $teamMember->delete();

        $this->productCacheInvalidator->flushProductDetails();
    }
}
