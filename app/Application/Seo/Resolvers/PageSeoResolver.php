<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Seo\Actions\SeoData\ResolvePageSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Content\Models\Page;

class PageSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolvePageSeoDataAction $resolvePageSeoDataAction,
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
            && $resolvedContent->isPage()
            && $resolvedContent->getPage() instanceof Page;
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $resolvedContent = $context->request->route('slug');

        if (!$resolvedContent instanceof ResolvedContentDTO) {
            throw new \RuntimeException('Resolved content is not available.');
        }

        $page = $resolvedContent->getPage();

        if (!$page instanceof Page) {
            throw new \RuntimeException('Resolved content is not a page.');
        }

        return $this->resolvePageSeoDataAction->execute($page);
    }
}
