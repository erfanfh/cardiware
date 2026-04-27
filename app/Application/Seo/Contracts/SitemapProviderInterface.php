<?php

namespace App\Application\Seo\Contracts;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;

interface SitemapProviderInterface
{
    public function key(): string;

    public function isEnabled(): bool;

    public function supports(string $type): bool;

    public function totalUrls(): int;

    public function totalPages(): int;

    public function hasPage(int $page): bool;

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function indexItems(): array;

    /**
     * @return array<int, SitemapUrlDTO>
     */
    public function urls(int $page = 1): array;
}
