<?php

namespace App\Application\Project\Actions;

use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProjectAction
{
    public function __construct(
        protected SyncModelTagsAction           $syncModelTagsAction,
        protected SyncProjectFeaturesAction     $syncProjectFeaturesAction,
        protected SyncProjectRoadmapItemsAction $syncProjectRoadmapItemsAction,
        protected SyncProjectChangelogsAction   $syncProjectChangelogsAction,
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
            unset($projectData['features']);
            unset($projectData['roadmap_items']);
            unset($projectData['changelogs']);

            $project = Project::create($data);

            $this->syncModelTagsAction->execute($project, $data['tags'] ?? []);
            $this->syncProjectFeaturesAction->execute($project, $data['features'] ?? []);
            $this->syncProjectRoadmapItemsAction->execute($project, $data['roadmap_items'] ?? []);
            $this->syncProjectChangelogsAction->execute($project, $data['changelogs'] ?? []);

            return $project;
        });
    }
}
