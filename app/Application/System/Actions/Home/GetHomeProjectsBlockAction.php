<?php

namespace App\Application\System\Actions\Home;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetHomeProjectsBlockAction
{
    /**
     * @return array{
     *     title:string,
     *     items:Collection<int, Project>
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::HOME,
            CacheTag::PROJECTS,
            CacheTag::HOME_PROJECTS,
        ])->remember(
            CacheKey::homeProjectsBlock(),
            CacheTtl::short(),
            function () {
                $items = collect(get_setting('home', 'projects_items') ?? []);

                $projectIds = $items
                    ->pluck('project_id')
                    ->filter()
                    ->unique()
                    ->values();

                $projects = Project::query()
                    ->whereIn('id', $projectIds)
                    ->get()
                    ->keyBy('id');

                $orderedProjects = $items
                    ->map(fn($item) => $projects->get($item['project_id'] ?? null))
                    ->filter()
                    ->values();

                return [
                    'title' => (string)get_setting('home', 'projects_title', 'پروژه‌ها'),
                    'items' => $orderedProjects,
                ];
            }
        );
    }
}
