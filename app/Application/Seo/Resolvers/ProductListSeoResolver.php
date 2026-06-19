<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProductListSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

final class ProductListSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProductListSeoDataAction $resolveProductListSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('products.list')
            || $context->routeIs('products.list.page');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolveProductListSeoDataAction->execute();
    }
}
