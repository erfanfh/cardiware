<?php

namespace App\Application\Project\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\Cache;

class GetProjectDetailAction
{
    public function execute(Project $project): Project
    {
        return Cache::tags([
            CacheTag::PROJECTS,
            CacheTag::PROJECT_DETAIL,
            CacheTag::project($project->id),
        ])->remember(
            CacheKey::projectDetail(
                projectId: $project->id,
                updatedAt: $project->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn () => $project
                ->load([
                    'cover.primaryVariant',
                    'roadmapItems',
                    'changelogs',
                    'features',
                    'tags',
                ])
        );
    }
}
