<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\ResolvePostSeoDataAction;
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
        return $context->routeIs('contents.detail');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        /** @var Post $post */
        $post = Post::where('slug', $context->request->slug)->first();

        return $this->resolvePostSeoDataAction->execute($post);
    }
}
