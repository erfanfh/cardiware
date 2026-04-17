<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\ResolveHomePageSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

class HomePageSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveHomePageSeoDataAction $resolveHomePageSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('home');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolveHomePageSeoDataAction->execute();
    }
}
