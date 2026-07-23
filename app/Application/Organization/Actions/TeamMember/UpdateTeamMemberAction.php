<?php

namespace App\Application\Organization\Actions\TeamMember;

use App\Domain\Organization\Models\TeamMember;

class UpdateTeamMemberAction
{
    public function execute(
        TeamMember $teamMember,
        array      $data
    ): TeamMember
    {
        $teamMember->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'job_title' => $data['job_title'] ?? null,
            'bio' => $data['bio'] ?? null,
            'picture_id' => $data['picture_id'] ?? null,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'website_url' => $data['website_url'] ?? null,
        ]);

        return $teamMember->refresh();
    }
}
