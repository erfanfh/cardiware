<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolvePostSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Content\Models\Post;

class PostSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolvePostSeoDataAction $resolvePostSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('contents.detail') and Post::where('slug', $context->request->slug)->exists();
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        return $this->resolvePostSeoDataAction->execute();
    }
}
