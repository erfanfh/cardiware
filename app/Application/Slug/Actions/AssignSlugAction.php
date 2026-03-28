<?php

namespace App\Application\Slug\Actions;

use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Slug\DTOs\AssignSlugResultDTO;
use App\Application\Slug\Services\SlugAssignmentManager;

class AssignSlugAction
{
    public function __construct(
        private readonly SlugAssignmentManager $manager,
    )
    {
    }

    /**
     * Execute the slug assignment workflow.
     */
    public function execute(AssignSlugDTO $dto): AssignSlugResultDTO
    {
        return $this->manager->assign($dto);
    }
}
