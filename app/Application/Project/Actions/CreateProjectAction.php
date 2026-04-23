<?php

namespace App\Application\Project\Actions;

use App\Application\Seo\Actions\Analysis\BuildProjectSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelProjectTagsAction;
use App\Domain\Project\Models\Project;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProjectAction
{
    public function __construct(
        protected SyncModelProjectTagsAction    $syncModelProjectTagsAction,
        protected SyncProjectFeaturesAction     $syncProjectFeaturesAction,
        protected SyncProjectRoadmapItemsAction $syncProjectRoadmapItemsAction,
        protected SyncProjectChangelogsAction   $syncProjectChangelogsAction,
        protected AssignSlugAction              $assignSlugAction,
        protected SyncSeoMetaAction             $syncSeoMetaAction,
        protected StoreSeoAnalysisAction        $storeSeoAnalysisAction,
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
        $data['title'] = $data['title'] ?? 'بدون عنوان';
        $data['body'] = $data['body'] ?? 'بدون متن';
        $data['slug'] = $data['slug'] ?? 'untitled-page';

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

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::PROJECT,
                    model: $project,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelProjectTagsAction->execute($project, $data['tags'] ?? []);
            $this->syncProjectFeaturesAction->execute($project, $data['features'] ?? []);
            $this->syncProjectRoadmapItemsAction->execute($project, $data['roadmap_items'] ?? []);
            $this->syncProjectChangelogsAction->execute($project, $data['changelogs'] ?? []);

            $this->syncSeoMetaAction->execute($project, $data['seo'] ?? []);
            $this->storeSeoAnalysisAction->execute($project, app(BuildProjectSeoAnalysisInputAction::class));

            return $project;
        });
    }
}
