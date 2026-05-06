<?php

namespace App\Application\Seo\Contracts;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;

interface StaticSitemapGeneratorInterface
{
    public function key(): string;

    public function isEnabled(): bool;

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function generate(): array;
}
