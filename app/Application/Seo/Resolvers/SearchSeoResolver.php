<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveSearchSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

final class SearchSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveSearchSeoDataAction $resolveSearchSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('search');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolveSearchSeoDataAction->execute();
    }
}
