<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\SeoData\ResolveAuthorSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Identity\Models\User;

class AuthorSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly ResolveAuthorSeoDataAction $resolveAuthorSeoDataAction,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('author')
            && $context->request->route('user') instanceof User;
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $author = $context->request->route('user');

        if (!$author instanceof User) {
            throw new \RuntimeException('Resolved content is not an author.');
        }

        return $this->resolveAuthorSeoDataAction->execute($author);
    }
}
