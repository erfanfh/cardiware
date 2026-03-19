<?php

namespace App\Application\Project\Actions;

use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProjectAction
{
    public function __construct(
        protected SyncModelTagsAction $syncModelTagsAction
    )
    {
    }

    /**
     * @param Project $project
     * @param array $data
     * @return Project
     * @throws Throwable
     */
    public function execute(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $projectData = $data;
            unset($projectData['tags']);

            $project->update($projectData);

            $this->syncModelTagsAction->execute(
                model: $project,
                payload: $data['tags'] ?? []
            );

            return $project;
        });
    }
}
