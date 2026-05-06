<?php

namespace App\Application\Seo\Support\Sitemap;

use App\Application\Seo\Contracts\StaticSitemapGeneratorInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Renderers\SitemapXmlRenderer;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractStaticSitemapGenerator implements StaticSitemapGeneratorInterface
{
    public function __construct(
        protected readonly SitemapStorage     $storage,
        protected readonly SitemapXmlRenderer $renderer,
    )
    {
    }

    abstract protected function query(): Builder;

    abstract protected function filenameForPage(int $page): string;

    abstract protected function sitemapUrlForPage(int $page): string;

    abstract protected function mapModelToUrl(Model $model): SitemapUrlDTO;

    public function isEnabled(): bool
    {
        return (bool)config("seo-sitemap.types.{$this->key()}.enabled", true);
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function generate(): array
    {
        $indexItems = [];
        $page = 1;

        $this->query()
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function (Collection $models) use (&$page, &$indexItems) {
                $urls = $models
                    ->map(fn(Model $model) => $this->mapModelToUrl($model))
                    ->values()
                    ->all();

                if (empty($urls)) {
                    return true;
                }

                $filename = $this->filenameForPage($page);

                $this->storage->put(
                    $filename,
                    $this->renderer->urlset($urls)
                );

                $indexItems[] = new SitemapIndexItemDTO(
                    url: $this->sitemapUrlForPage($page),
                    lastmod: $this->lastmodFromUrls($urls),
                );

                $page++;

                return true;
            });

        return $indexItems;
    }

    protected function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    /**
     * @param array<int, SitemapUrlDTO> $urls
     */
    protected function lastmodFromUrls(array $urls): ?CarbonInterface
    {
        return collect($urls)
            ->pluck('lastmod')
            ->filter()
            ->sortDesc()
            ->first();
    }
}
