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

final class ProductCategoryStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    /** @var array<int, CarbonInterface> */
    private array $lastModifiedByCategory = [];

    public function key(): string
    {
        return 'product_category';
    }

    protected function query(): Builder
    {
        $this->lastModifiedByCategory = [];

        $categories = TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->get(['id', 'parent_id', 'updated_at'])
            ->keyBy('id');

        $categoriesWithProducts = TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->whereHas('products', fn (Builder $query) => $this->publishedProducts($query))
            ->withMax([
                'products as latest_product_updated_at' => fn (Builder $query) => $this->publishedProducts($query),
            ], 'updated_at')
            ->get(['id']);

        $includedIds = [];

        foreach ($categoriesWithProducts as $category) {
            $lastModified = $category->latest_product_updated_at
                ? Carbon::parse($category->latest_product_updated_at)
                : null;
            $currentId = (int) $category->getKey();

            while ($currentId > 0 && $categories->has($currentId)) {
                $includedIds[$currentId] = $currentId;
                $termUpdatedAt = $categories->get($currentId)?->updated_at;
                $candidate = $lastModified;

                if ($termUpdatedAt && (! $candidate || $termUpdatedAt->greaterThan($candidate))) {
                    $candidate = $termUpdatedAt;
                }

                if ($candidate && (
                    ! isset($this->lastModifiedByCategory[$currentId])
                    || $candidate->greaterThan($this->lastModifiedByCategory[$currentId])
                )) {
                    $this->lastModifiedByCategory[$currentId] = $candidate;
                }

                $currentId = (int) ($categories->get($currentId)?->parent_id ?? 0);
            }
        }

        return TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::PRODUCT_CATEGORY)
            ->whereIn('id', array_values($includedIds))
            ->select(['id', 'parent_id', 'taxonomy', 'title', 'path', 'updated_at']);
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'product-category-sitemap.xml'
            : "product-category-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return url($page === 1
            ? '/product-category-sitemap.xml'
            : "/product-category-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var TaxonomyTerm $model */
        return new SitemapUrlDTO(
            url: front_route('products.categories', ['path' => $model->path]),
            lastmod: $this->lastModifiedByCategory[(int) $model->getKey()] ?? $model->updated_at,
            images: [],
        );
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
