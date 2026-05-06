<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoryStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'category';
    }

    protected function query(): Builder
    {
        return TaxonomyTerm::query()
            ->where('taxonomy', TaxonomyKeyEnum::POST_CATEGORY)
            ->select([
                'id',
                'parent_id',
                'taxonomy',
                'title',
                'slug',
                'path',
                'updated_at',
            ])
            ->withMax([
                'posts as latest_post_updated_at' => function (Builder $query): void {
                    $query->where('status', PostStatusEnum::ACTIVE);
                },
            ], 'updated_at');
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'category-sitemap.xml'
            : "category-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/category-sitemap.xml')
            : url("/category-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var TaxonomyTerm $term */
        $term = $model;

        return new SitemapUrlDTO(
            url: front_route('posts.categories', $term),
            lastmod: $this->lastmod($term),
            images: [],
        );
    }

    private function lastmod(TaxonomyTerm $term): ?CarbonInterface
    {
        return $term->latest_post_updated_at
            ? Carbon::parse($term->latest_post_updated_at)
            : $term->updated_at;
    }
}
