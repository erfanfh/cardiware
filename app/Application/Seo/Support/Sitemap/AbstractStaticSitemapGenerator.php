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
     * @return array<int, SitemapUrlDTO>
     */
    protected function firstPageStaticUrls(): array
    {
        return [];
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function generate(): array
    {
        $indexItems = [];
        $page = 1;
        $hasGeneratedAnyFile = false;

        $this->query()
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function (Collection $models) use (&$page, &$indexItems, &$hasGeneratedAnyFile) {
                $urls = $models
                    ->map(fn(Model $model) => $this->mapModelToUrl($model))
                    ->values()
                    ->all();

                if ($page === 1) {
                    $urls = [
                        ...$this->firstPageStaticUrls(),
                        ...$urls,
                    ];
                }

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

                $hasGeneratedAnyFile = true;
                $page++;

                usleep(200000);

                return true;
            });

        if (!$hasGeneratedAnyFile) {
            $urls = $this->firstPageStaticUrls();

            if (!empty($urls)) {
                $filename = $this->filenameForPage(1);

                $this->storage->put(
                    $filename,
                    $this->renderer->urlset($urls)
                );

                $indexItems[] = new SitemapIndexItemDTO(
                    url: $this->sitemapUrlForPage(1),
                    lastmod: $this->lastmodFromUrls($urls),
                );
            }
        }

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
