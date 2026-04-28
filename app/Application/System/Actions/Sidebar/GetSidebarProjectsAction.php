<?php

namespace App\Application\System\Actions\Sidebar;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetSidebarProjectsAction
{
    /**
     * @return array{
     *     title:string,
     *     subtitle:string,
     *     projects:Collection<int, Project>
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_PROJECTS,
            CacheTag::PROJECTS,
        ])->remember(
            CacheKey::sidebarProjects(),
            CacheTtl::medium(),
            function () {
                $items = collect(get_setting('sidebarWidgets', 'projects_items') ?? []);

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
                    'title' => (string)get_setting('sidebarWidgets', 'projects_title'),
                    'subtitle' => (string)get_setting('sidebarWidgets', 'projects_subtitle'),
                    'projects' => $orderedProjects,
                ];
            }
        );
    }
}
