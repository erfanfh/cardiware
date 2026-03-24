<?php

namespace App\Application\Project\Actions;

use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProjectAction
{
    public function __construct(
        protected SyncModelTagsAction           $syncModelTagsAction,
        protected SyncProjectFeaturesAction     $syncProjectFeaturesAction,
        protected SyncProjectRoadmapItemsAction $syncProjectRoadmapItemsAction,
        protected SyncProjectChangelogsAction   $syncProjectChangelogsAction,
        protected SyncProjectAdsAction   $syncProjectAdsAction,
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
            unset($projectData['features']);
            unset($projectData['roadmap_items']);
            unset($projectData['changelogs']);
            unset($projectData['ads']);

            $project->update($projectData);

            $this->syncModelTagsAction->execute($project, $data['tags'] ?? []);
            $this->syncProjectFeaturesAction->execute($project, $data['features'] ?? []);
            $this->syncProjectRoadmapItemsAction->execute($project, $data['roadmap_items'] ?? []);
            $this->syncProjectChangelogsAction->execute($project, $data['changelogs'] ?? []);
            $this->syncProjectAdsAction->execute($project, $data['ads'] ?? []);

            return $project;
        });
    }
}
