<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\StableSitemapPaginator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class TagSitemapProvider implements SitemapProviderInterface
{
    public function __construct(
        private readonly StableSitemapPaginator $paginator,
    )
    {
    }

    public function key(): string
    {
        return 'tag';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.tag.enabled', true);
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
            ->map(fn(TaxonomyTerm $term) => new SitemapUrlDTO(
                url: $this->tagUrl($term),
                lastmod: $this->tagLastmod($term),
                images: [],
            ))
            ->values()
            ->all();
    }

    private function allChunkableQuery(): Builder
    {
        return TaxonomyTerm::query()
            ->withTrashed()
            ->where('taxonomy', TaxonomyKeyEnum::POST_TAG);
    }

    private function baseIndexableQuery(): Builder
    {
        return TaxonomyTerm::query()
            ->where('taxonomy', TaxonomyKeyEnum::POST_TAG);
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
            ->select([
                'id',
                'slug',
                'updated_at',
            ]);
    }

    /**
     * @return array<int, int>
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
     * @return array<int, int>
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

        $postLastmod = $this->postsForTermsQuery($ids)
            ->max('updated_at');

        $termLastmod = $this->baseIndexableQuery()
            ->whereIn('id', $ids)
            ->max('updated_at');

        $lastmod = collect([$postLastmod, $termLastmod])
            ->filter()
            ->map(fn($date) => Carbon::parse($date))
            ->sortDesc()
            ->first();

        return $lastmod ?: null;
    }

    private function tagLastmod(TaxonomyTerm $term): ?CarbonInterface
    {
        $lastmod = $this->postsForTermsQuery([$term->id])
            ->max('updated_at');

        return $lastmod
            ? Carbon::parse($lastmod)
            : $term->updated_at;
    }

    /**
     * @param array<int,int> $termIds
     */
    private function postsForTermsQuery(array $termIds): Builder
    {
        return Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->whereHas('tags', function (Builder $query) use ($termIds) {
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
            ? url('/post_tag-sitemap.xml')
            : url("/post_tag-sitemap{$page}.xml");
    }

    private function tagUrl(TaxonomyTerm $term): string
    {
        return url('/tag/' . trim($term->slug, '/') . '/');
    }
}
