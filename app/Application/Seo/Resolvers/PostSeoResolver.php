<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Seo\Actions\SeoData\ResolvePostSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

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
            throw new RuntimeException('Resolved content is not available.');
        }

        $post = $resolvedContent->getPost();

        if (!$post instanceof Post) {
            throw new RuntimeException('Resolved content is not a post.');
        }

        return Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::POSTS,
            CacheTag::post($post->id),
        ])->remember(
            CacheKey::postSeoData(
                postId: $post->id,
                postUpdatedAt: $post->updated_at?->timestamp ?? 0,
                seoMetaUpdatedAt: $post->seoMeta?->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $this->resolvePostSeoDataAction->execute($post)
        );
    }
}
