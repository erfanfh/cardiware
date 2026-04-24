<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolvePostListSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

final class PostListSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolvePostListSeoDataAction $resolvePostListSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('posts.list');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolvePostListSeoDataAction->execute();
    }
}
