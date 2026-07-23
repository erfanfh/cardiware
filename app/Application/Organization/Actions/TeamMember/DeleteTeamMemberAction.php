<?php

namespace App\Application\Organization\Actions\TeamMember;

use App\Domain\Organization\Models\TeamMember;

class DeleteTeamMemberAction
{
    public function execute(TeamMember $teamMember): void
    {
        $teamMember->delete();
    }
}
