<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveAuthSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

final class AuthSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveAuthSeoDataAction $resolveAuthSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('auth.index');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolveAuthSeoDataAction->execute();
    }
}
