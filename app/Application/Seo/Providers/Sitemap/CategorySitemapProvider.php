<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class CategorySitemapProvider implements SitemapProviderInterface
{
    public function key(): string
    {
        return 'category';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.category.enabled', true);
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
            ->whereBetween('id', $this->idRangeForPage($page))
            ->orderBy('id')
            ->get()
            ->map(fn(TaxonomyTerm $term) => new SitemapUrlDTO(
                url: $this->categoryUrl($term),
                lastmod: $this->categoryLastmod($term),
                images: [],
            ))
            ->values()
            ->all();
    }

    private function baseIndexableQuery(): Builder
    {
        return TaxonomyTerm::query()
            ->where('taxonomy', TaxonomyKeyEnum::POST_CATEGORY);
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
            ->select([
                'id',
                'parent_id',
                'taxonomy',
                'title',
                'slug',
                'updated_at',
            ])
            ->with([
                'parent',
                'parent.parent',
                'parent.parent.parent',
                'parent.parent.parent.parent',
            ]);
    }

    /**
     * @return array<int, int>
     */
    private function availablePages(): array
    {
        $chunkSize = $this->chunkSize();

        return $this->baseIndexableQuery()
            ->selectRaw("FLOOR((id - 1) / {$chunkSize}) + 1 as sitemap_page")
            ->groupByRaw("FLOOR((id - 1) / {$chunkSize}) + 1")
            ->orderBy('sitemap_page')
            ->pluck('sitemap_page')
            ->map(fn($page) => (int)$page)
            ->values()
            ->all();
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function idRangeForPage(int $page): array
    {
        return [
            (($page - 1) * $this->chunkSize()) + 1,
            $page * $this->chunkSize(),
        ];
    }

    private function lastmodForPage(int $page): ?CarbonInterface
    {
        $terms = $this->baseIndexableQuery()
            ->whereBetween('id', $this->idRangeForPage($page))
            ->pluck('id');

        if ($terms->isEmpty()) {
            return null;
        }

        $lastmod = $this->postsForTermsQuery($terms->all())
            ->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function categoryLastmod(TaxonomyTerm $term): ?CarbonInterface
    {
        $lastmod = $this->postsForTermsQuery([$term->id])
            ->max('updated_at');

        return $lastmod
            ? Carbon::parse($lastmod)
            : $term->updated_at;
    }

    /**
     * @param array<int, int> $termIds
     */
    private function postsForTermsQuery(array $termIds): Builder
    {
        return Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->whereHas('categories', function (Builder $query) use ($termIds) {
                $query->whereIn('taxonomy_terms.id', $termIds);
            });
    }

    private function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/category-sitemap.xml')
            : url("/category-sitemap{$page}.xml");
    }

    private function categoryUrl(TaxonomyTerm $term): string
    {
        return url('/category/' . $this->termPath($term) . '/');
    }

    private function termPath(TaxonomyTerm $term): string
    {
        $segments = [];

        $current = $term;

        while ($current) {
            array_unshift($segments, trim($current->slug, '/'));

            $current = $current->parent;
        }

        return collect($segments)
            ->filter()
            ->implode('/');
    }
}
