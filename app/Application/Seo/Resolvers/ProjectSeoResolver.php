<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProjectSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Project\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProjectSeoDataAction $resolveProjectSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('projects.detail');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $slug = $context->request->route('projectSlug');
        $project = Project::where('slug', $slug)->firstOrFail();

        return Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::PROJECTS,
            CacheTag::project($project->id),
        ])->remember(
            CacheKey::projectSeoData(
                projectId: $project->id,
                projectUpdatedAt: $project->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $this->resolveProjectSeoDataAction->execute($project)
        );
    }
}
