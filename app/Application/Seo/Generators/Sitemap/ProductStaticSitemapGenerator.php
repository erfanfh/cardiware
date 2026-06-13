<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'product';
    }

    protected function query(): Builder
    {
        return Product::query()
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('slug')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->select([
                'id',
                'slug',
                'title',
                'body',
                'status',
                'published_at',
                'updated_at',
                'picture_id',
            ])
            ->with([
                'picture',
                'imageGallery.mediaFile.primaryVariant',
            ]);
    }

    /**
     * Generate product sitemap files and return their sitemap index entries.
     *
     * The first product sitemap always starts with the product listing page.
     * If there are no published products, the listing page is still written to product-sitemap.xml.
     *
     * @return array<int, SitemapIndexItemDTO>
     */
    public function generate(): array
    {
        $indexItems = [];
        $page = 1;
        $hasGeneratedAnyFile = false;

        $this->query()
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function (Collection $products) use (&$page, &$indexItems, &$hasGeneratedAnyFile): bool {
                $urls = [];

                if ($page === 1 && $this->shouldIncludeListingInFirstPage()) {
                    $urls[] = $this->productListingUrl();
                }

                foreach ($products as $product) {
                    $urls[] = $this->mapModelToUrl($product);
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

                return true;
            });

        if (!$hasGeneratedAnyFile && $this->shouldIncludeListingInFirstPage()) {
            $urls = [
                $this->productListingUrl(),
            ];

            $this->storage->put(
                $this->filenameForPage(1),
                $this->renderer->urlset($urls)
            );

            $indexItems[] = new SitemapIndexItemDTO(
                url: $this->sitemapUrlForPage(1),
                lastmod: $this->lastmodFromUrls($urls),
            );
        }

        return $indexItems;
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'product-sitemap.xml'
            : "product-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/product-sitemap.xml')
            : url("/product-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Product $product */
        $product = $model;

        return new SitemapUrlDTO(
            url: front_route('products.detail', $product),
            lastmod: $product->updated_at ?: $product->published_at,
            images: $this->productImages($product),
        );
    }

    private function productListingUrl(): SitemapUrlDTO
    {
        return new SitemapUrlDTO(
            url: front_route('products.list'),
            lastmod: $this->productListingLastmod(),
            images: [],
        );
    }

    private function shouldIncludeListingInFirstPage(): bool
    {
        return (bool)config('seo-sitemap.types.product.include_listing_in_first_page', true);
    }

    private function productListingLastmod(): ?CarbonInterface
    {
        $latestUpdatedAt = $this->publishedProductQuery()
            ->max('updated_at');

        if ($latestUpdatedAt) {
            return Carbon::parse($latestUpdatedAt);
        }

        $latestPublishedAt = $this->publishedProductQuery()
            ->max('published_at');

        return $latestPublishedAt
            ? Carbon::parse($latestPublishedAt)
            : null;
    }

    private function publishedProductQuery(): Builder
    {
        return Product::query()
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('slug')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Get all sitemap images for the given product.
     *
     * @return array<int, SitemapImageDTO>
     */
    private function productImages(Product $product): array
    {
        if (!$this->shouldIncludeImages()) {
            return [];
        }

        return collect()
            ->merge($this->mainImage($product))
            ->merge($this->galleryImages($product))
            ->merge($this->contentImages($product))
            ->filter()
            ->unique(fn(SitemapImageDTO $image) => $image->url)
            ->values()
            ->take(20)
            ->all();
    }

    private function shouldIncludeImages(): bool
    {
        return (bool)config('seo-sitemap.include_images', true)
            && (bool)config('seo-sitemap.types.product.include_images', true);
    }

    /**
     * Get the main product image.
     *
     * @return array<int, SitemapImageDTO>
     */
    private function mainImage(Product $product): array
    {
        if (!$product->picture?->url) {
            return [];
        }

        return [
            new SitemapImageDTO(
                url: $this->absoluteUrl($product->picture->url),
            ),
        ];
    }

    /**
     * Get product image gallery items.
     *
     * @return array<int, SitemapImageDTO>
     */
    private function galleryImages(Product $product): array
    {
        return $product->imageGallery
            ->filter(fn($imageGallery) => filled($imageGallery->mediaFile?->url))
            ->map(fn($imageGallery) => new SitemapImageDTO(
                url: $this->absoluteUrl($imageGallery->mediaFile->url),
            ))
            ->values()
            ->all();
    }

    /**
     * Extract image URLs from the product body.
     *
     * @return array<int, SitemapImageDTO>
     */
    private function contentImages(Product $product): array
    {
        if (blank($product->body)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $product->body, $matches);

        return collect($matches[1] ?? [])
            ->filter()
            ->map(fn(string $url) => new SitemapImageDTO(
                url: $this->absoluteUrl($url),
            ))
            ->values()
            ->all();
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
