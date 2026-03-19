<?php

namespace App\Application\Project\Actions;

use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProjectAction
{
    public function __construct(
        protected SyncModelTagsAction $syncModelTagsAction
    )
    {
    }

    /**
     * @param array $data
     * @return Project
     * @throws Throwable
     */
    public function execute(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $projectData = $data;
            unset($projectData['tags']);
            
            $project = Project::create($data);

            $this->syncModelTagsAction->execute(
                model: $project,
                payload: $data['tags'] ?? []
            );

            return $project;
        });
    }
}
