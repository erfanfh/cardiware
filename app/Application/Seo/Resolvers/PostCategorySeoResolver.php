<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolvePostCategorySeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

class PostCategorySeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolvePostCategorySeoDataAction $resolvePostCategorySeoDataAction,
        private readonly ResolveSlugAction                $resolveSlugAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('posts.categories');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $path = $context->request->route('path');

        $resolvedSlug = $this->resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: SlugScopeEnum::TAXONOMY__POST_CATEGORY,
                value: (string)$path,
            )
        );

        if (!$resolvedSlug->found || !$resolvedSlug->model instanceof TaxonomyTerm) {
            throw new \RuntimeException('Resolved content is not a post category.');
        }

        /** @var TaxonomyTerm $category */
        $category = $resolvedSlug->model;

        return $this->resolvePostCategorySeoDataAction->execute($category);
    }
}
