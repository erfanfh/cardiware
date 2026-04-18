<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Content\DTOs\ResolvedContentDTO;
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
        if (!$context->routeIs('contents.detail')) {
            return false;
        }

        $resolvedContent = $context->request->route('slug');

        return $resolvedContent instanceof ResolvedContentDTO
            && $resolvedContent->isPost()
            && $resolvedContent->getPost() instanceof Post;
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $resolvedContent = $context->request->route('slug');

        if (!$resolvedContent instanceof ResolvedContentDTO) {
            throw new \RuntimeException('Resolved content is not available.');
        }

        $post = $resolvedContent->getPost();

        if (!$post instanceof Post) {
            throw new \RuntimeException('Resolved content is not a post.');
        }

        return $this->resolvePostSeoDataAction->execute($post);
    }
}
