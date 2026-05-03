<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolvePostTagSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

class PostTagSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolvePostTagSeoDataAction $resolvePostTagSeoDataAction,
        private readonly ResolveSlugAction           $resolveSlugAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('posts.tags')
            || $context->routeIs('posts.tags.page');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $path = $context->request->route('path');

        $resolvedSlug = $this->resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: SlugScopeEnum::TAXONOMY__POST_TAG,
                value: (string)$path,
            )
        );

        if (!$resolvedSlug->found || !$resolvedSlug->model instanceof TaxonomyTerm) {
            throw new \RuntimeException('Resolved content is not a post tag.');
        }

        /** @var TaxonomyTerm $tag */
        $tag = $resolvedSlug->model;

        $paginator = $context->request->attributes->get('seo_paginator');
        $paginationBaseUrl = $context->request->attributes->get('seo_pagination_base_url');

        return $this->resolvePostTagSeoDataAction->execute(
            tag: $tag,
            paginator: $paginator,
            paginationBaseUrl: $paginationBaseUrl,
        );
    }
}
