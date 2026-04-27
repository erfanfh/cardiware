<?php

namespace App\Application\Seo\Actions\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\Support\Sitemap\SitemapProviderRegistry;

class BuildSitemapIndexAction
{
    public function __construct(
        private readonly SitemapProviderRegistry $registry,
    )
    {
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function execute(): array
    {
        return $this->registry
            ->enabledProviders()
            ->flatMap(fn($provider) => $provider->indexItems())
            ->values()
            ->all();
    }
}
