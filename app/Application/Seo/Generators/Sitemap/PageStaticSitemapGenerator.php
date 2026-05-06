<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'page';
    }

    protected function query(): Builder
    {
        return Page::query()
            ->where('status', PageStatusEnum::ACTIVE)
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

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'page-sitemap.xml'
            : "page-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/page-sitemap.xml')
            : url("/page-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Page $page */
        $page = $model;

        return new SitemapUrlDTO(
            url: $page->url,
            lastmod: $page->updated_at,
            images: $this->pageImages($page),
        );
    }

    /**
     * @return array<int, SitemapImageDTO>
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
