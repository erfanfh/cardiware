<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveProductTagSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use RuntimeException;

final class ProductTagSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveProductTagSeoDataAction $resolveProductTagSeoDataAction,
        private readonly ResolveSlugAction $resolveSlugAction,
    ) {}

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('products.tags', 'products.tags.page');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $resolvedSlug = $this->resolveSlugAction->execute(new ResolveSlugDTO(
            scope: SlugScopeEnum::TAXONOMY__PRODUCT_TAG,
            value: (string) $context->request->route('path'),
        ));

        if (! $resolvedSlug->found || ! $resolvedSlug->model instanceof TaxonomyTerm) {
            throw new RuntimeException('Resolved content is not a product tag.');
        }

        return $this->resolveProductTagSeoDataAction->execute(
            tag: $resolvedSlug->model,
            paginator: $context->request->attributes->get('seo_paginator'),
            paginationBaseUrl: $context->request->attributes->get('seo_pagination_base_url'),
        );
    }
}
