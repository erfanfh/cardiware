<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectCacheInvalidator
{
    public function __construct(
        protected SeoCacheInvalidator  $seoCacheInvalidator,
        protected HomeCacheInvalidator $homeCacheInvalidator,
    )
    {
    }

    public function afterCreated(Project $project): void
    {
        $this->flushProjectRelatedCaches($project);
    }

    public function afterUpdated(Project $project): void
    {
        $this->flushProjectRelatedCaches($project);
    }

    public function afterDeleted(Project $project): void
    {
        $this->flushProjectRelatedCaches($project);
    }

    protected function flushProjectRelatedCaches(Project $project): void
    {
        $this->flushProjectSeoCaches($project);
        $this->flushProjectDetailCaches($project);
        $this->flushProjectListCaches($project);
        $this->flushProjectSidebarCaches();
        $this->flushProjectHomeCaches();
    }

    protected function flushProjectSeoCaches(Project $project): void
    {
        Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::PROJECTS,
            CacheTag::project($project->id),
        ])->flush();
    }

    protected function flushProjectDetailCaches(Project $project): void
    {
        Cache::tags([
            CacheTag::PROJECT_DETAIL,
            CacheTag::project($project->id),
        ])->flush();
    }

    protected function flushProjectListCaches(Project $project): void
    {
        Cache::tags([
            CacheTag::PROJECTS,
            CacheTag::project($project->id),
        ])->flush();
    }

    protected function flushProjectSidebarCaches(): void
    {
        Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_PROJECTS,
        ])->flush();
    }

    protected function flushProjectHomeCaches(): void
    {
        Cache::tags([
            CacheTag::HOME,
            CacheTag::HOME_PROJECTS,
        ])->flush();
    }
}
