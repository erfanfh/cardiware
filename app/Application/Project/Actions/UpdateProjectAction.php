<?php

namespace App\Application\Project\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
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
        protected AssignSlugAction              $assignSlugAction,
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

            unset(
                $projectData['tags'],
                $projectData['features'],
                $projectData['roadmap_items'],
                $projectData['changelogs'],
                $projectData['slug'],
            );

            $project->update($projectData);

            $desiredSlug = trim((string)(($data['slug'] ?: $data['title']) ?? ''));

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: 'project',
                    model: $project,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelTagsAction->execute($project, $data['tags'] ?? []);
            $this->syncProjectFeaturesAction->execute($project, $data['features'] ?? []);
            $this->syncProjectRoadmapItemsAction->execute($project, $data['roadmap_items'] ?? []);
            $this->syncProjectChangelogsAction->execute($project, $data['changelogs'] ?? []);

            return $project;
        });
    }
}
