<?php

namespace App\Application\Seo\Support;

use App\Application\Seo\Contracts\SeoResolverInterface;
use RuntimeException;

class SeoResolverRegistry
{
    /**
     * @param array<int, SeoResolverInterface> $resolvers
     */
    public function __construct(
        private readonly array $resolvers,
    )
    {
    }

    public function resolve(SeoContext $context): ?SeoResolverInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($context)) {
                return $resolver;
            }
        }

        return null;
    }

    public function resolveOrFail(SeoContext $context): SeoResolverInterface
    {
        $resolver = $this->resolve($context);

        if ($resolver) {
            return $resolver;
        }

        throw new RuntimeException('No SEO resolver matched the current context.');
    }
}
