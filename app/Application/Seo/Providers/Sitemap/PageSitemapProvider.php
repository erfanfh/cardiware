<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\StableSitemapPaginator;
use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PageSitemapProvider implements SitemapProviderInterface
{
    public function __construct(
        private readonly StableSitemapPaginator $paginator,
    )
    {
    }

    public function key(): string
    {
        return 'page';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.page.enabled', true);
    }

    public function supports(string $type): bool
    {
        return $type === $this->key();
    }

    public function totalUrls(): int
    {
        return $this->baseIndexableQuery()->count();
    }

    public function totalPages(): int
    {
        return count($this->availablePages());
    }

    public function hasPage(int $page): bool
    {
        return in_array($page, $this->availablePages(), true);
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function indexItems(): array
    {
        return collect($this->availablePages())
            ->map(fn(int $page) => new SitemapIndexItemDTO(
                url: $this->sitemapUrl($page),
                lastmod: $this->lastmodForPage($page),
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, SitemapUrlDTO>
     */
    public function urls(int $page = 1): array
    {
        return $this->sitemapContentQuery()
            ->whereIn('id', $this->idsForPage($page))
            ->orderBy('id')
            ->get()
            ->map(fn(Page $pageModel) => new SitemapUrlDTO(
                url: $this->pageUrl($pageModel),
                lastmod: $this->pageLastmod($pageModel),
                images: $this->pageImages($pageModel),
            ))
            ->values()
            ->all();
    }

    private function allChunkableQuery(): Builder
    {
        return Page::query()->withTrashed();
    }

    private function baseIndexableQuery(): Builder
    {
        return Page::query()
            ->where('status', PageStatusEnum::ACTIVE);
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
            ->select([
                'id',
                'slug',
                'updated_at',
                'picture_id',
            ])
            ->with([
                'picture',
            ]);
    }

    /**
     * @return array<int,int>
     */
    private function availablePages(): array
    {
        return $this->paginator->publicPages(
            allItemsQuery: $this->allChunkableQuery(),
            indexableQuery: $this->baseIndexableQuery(),
            chunkSize: $this->chunkSize(),
        );
    }

    /**
     * @return array<int,int>
     */
    private function idsForPage(int $page): array
    {
        return $this->paginator->idsForPublicPage(
            allItemsQuery: $this->allChunkableQuery(),
            indexableQuery: $this->baseIndexableQuery(),
            publicPage: $page,
            chunkSize: $this->chunkSize(),
        );
    }

    private function lastmodForPage(int $page): ?CarbonInterface
    {
        $ids = $this->idsForPage($page);

        if (empty($ids)) {
            return null;
        }

        $lastmod = $this->baseIndexableQuery()
            ->whereIn('id', $ids)
            ->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/page-sitemap.xml')
            : url("/page-sitemap{$page}.xml");
    }

    private function pageUrl(Page $page): string
    {
        return url('/' . trim($page->slug, '/') . '/');
    }

    private function pageLastmod(Page $page): ?CarbonInterface
    {
        return $page->updated_at;
    }

    /**
     * @return array<int,SitemapImageDTO>
     */
    private function pageImages(Page $page): array
    {
        if (!config('seo-sitemap.types.page.include_images', true)) {
            return [];
        }

        if (!$page->picture?->url) {
            return [];
        }

        return [
            new SitemapImageDTO(
                url: $this->absoluteUrl($page->picture->url),
            ),
        ];
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            return request()->getScheme() . ':' . $url;
        }

        return url('/' . ltrim($url, '/'));
    }
}
