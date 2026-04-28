<?php

namespace App\Application\Project\Actions;

use App\Application\Shared\Cache\Invalidators\ProjectCacheInvalidator;
use App\Domain\Project\Models\Project;

class DeleteProjectAction
{
    public function __construct(
        protected ProjectCacheInvalidator $projectCacheInvalidator,
    )
    {
    }

    /**
     * Delete the given project.
     *
     * @param Project $project
     * @return void
     */
    public function execute(Project $project): void
    {
        $this->projectCacheInvalidator->afterDeleted($project);

        $project->delete();
    }
}
