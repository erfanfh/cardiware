<?php

namespace App\Application\Seo\Support\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use Illuminate\Support\Collection;

class SitemapProviderRegistry
{
    /**
     * @param iterable<int, SitemapProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    )
    {
    }

    /**
     * @return Collection<int, SitemapProviderInterface>
     */
    public function enabledProviders(): Collection
    {
        return collect($this->providers)
            ->filter(fn(SitemapProviderInterface $provider) => $provider->isEnabled())
            ->values();
    }

    public function findByType(string $type): ?SitemapProviderInterface
    {
        return $this->enabledProviders()
            ->first(fn(SitemapProviderInterface $provider) => $provider->supports($type));
    }
}
