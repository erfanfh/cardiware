<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProjectSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Project\Models\Project;

class ProjectSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProjectSeoDataAction $resolveProjectSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('projects.detail')
            && $context->request->route('project') instanceof Project;
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $project = $context->request->route('project');

        if (!$project instanceof Project) {
            throw new \RuntimeException('Resolved content is not a project.');
        }

        return $this->resolveProjectSeoDataAction->execute($project);
    }
}
