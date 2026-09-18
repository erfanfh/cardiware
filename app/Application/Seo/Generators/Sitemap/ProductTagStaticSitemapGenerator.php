<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ProductTagStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'product_tag';
    }

    protected function query(): Builder
    {
        return TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_TAG)
            ->whereHas('products', fn (Builder $query) => $this->publishedProducts($query))
            ->withMax([
                'products as latest_product_updated_at' => fn (Builder $query) => $this->publishedProducts($query),
            ], 'updated_at')
            ->select(['id', 'taxonomy', 'title', 'path', 'updated_at']);
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'product-tag-sitemap.xml'
            : "product-tag-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return url($page === 1
            ? '/product-tag-sitemap.xml'
            : "/product-tag-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var TaxonomyTerm $model */
        return new SitemapUrlDTO(
            url: front_route('products.tags', ['path' => $model->path]),
            lastmod: $this->lastModified($model),
            images: [],
        );
    }

    private function lastModified(TaxonomyTerm $tag): ?CarbonInterface
    {
        $latestProductUpdate = $tag->latest_product_updated_at
            ? Carbon::parse($tag->latest_product_updated_at)
            : null;

        if ($latestProductUpdate && $tag->updated_at?->greaterThan($latestProductUpdate)) {
            return $tag->updated_at;
        }

        return $latestProductUpdate ?: $tag->updated_at;
    }

    private function publishedProducts(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('slug')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('saleItems');
    }
}
