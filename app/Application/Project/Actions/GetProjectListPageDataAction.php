<?php

namespace App\Application\Project\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Project\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class GetProjectListPageDataAction
{
    /**
     * @return array{
     *     stats: object,
     *     projects: LengthAwarePaginator
     * }
     */
    public function execute(int $page = 1, int $perPage = 9): array
    {
        return [
            'stats' => $this->getStats(),
            'projects' => $this->getProjects($page, $perPage),
        ];
    }

    protected function getStats(): object
    {
        return Cache::tags([
            CacheTag::PROJECTS,
            CacheTag::PROJECT_STATS,
        ])->remember(
            CacheKey::projectStats(),
            CacheTtl::medium(),
            fn () => Project::query()
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN progress_percent = 0 THEN 1 ELSE 0 END) as not_started,
                    SUM(CASE WHEN progress_percent BETWEEN 1 AND 99 THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN progress_percent = 100 THEN 1 ELSE 0 END) as completed
                ')
                ->first()
        );
    }

    protected function getProjects(int $page, int $perPage): LengthAwarePaginator
    {
        $projects = Cache::tags([
            CacheTag::PROJECTS,
            CacheTag::PROJECT_LISTS,
        ])->remember(
            CacheKey::projectList($page, $perPage),
            CacheTtl::short(),
            fn () => Project::query()
                ->latest()
                ->paginate(
                    perPage: $perPage,
                    page: $page,
                )
        );

        return (clone $projects)->withPath(request()->url());
    }
}
