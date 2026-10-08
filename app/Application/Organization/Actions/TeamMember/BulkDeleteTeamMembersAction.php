<?php

namespace App\Application\Organization\Actions\TeamMember;

use App\Domain\Organization\Models\TeamMember;

class BulkDeleteTeamMembersAction
{
    /**
     * @param array<int, int> $ids
     */
    public function execute(array $ids): int
    {
        $members = TeamMember::query()
            ->whereKey($ids)
            ->get();
        $members->each->delete();

        return $members->count();
    }
}
