<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProjectListSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

final class ProjectListSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProjectListSeoDataAction $resolveProjectListSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('projects.list')
            || $context->routeIs('projects.list.page');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolveProjectListSeoDataAction->execute();
    }
}
