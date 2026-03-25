<?php

namespace App\Application\Project\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Models\Project;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProjectAction
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
     * Create a new project, assign its slug, and sync related data.
     *
     * @param array $data
     * @return Project
     * @throws Throwable
     */
    public function execute(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $projectData = $data;

            unset(
                $projectData['tags'],
                $projectData['features'],
                $projectData['roadmap_items'],
                $projectData['changelogs'],
                $projectData['slug'],
            );

            $project = Project::create($projectData);

            $desiredSlug = trim((string)(($data['slug'] ?: $data['title']) ?? ''));

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::PROJECT,
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
