<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\StableSitemapPaginator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class AuthorSitemapProvider implements SitemapProviderInterface
{
    public function __construct(
        private readonly StableSitemapPaginator $paginator,
    )
    {
    }

    public function key(): string
    {
        return 'author';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.author.enabled', true);
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
            ->map(fn(User $user) => new SitemapUrlDTO(
                url: $this->authorUrl($user),
                lastmod: $this->authorLastmod($user),
                images: [],
            ))
            ->values()
            ->all();
    }

    private function allChunkableQuery(): Builder
    {
        return User::query()
            ->withTrashed();
    }

    private function baseIndexableQuery(): Builder
    {
        return User::query()
            ->whereHas('posts', function (Builder $query) {
                $query->where('status', PostStatusEnum::ACTIVE);
            });
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
            ->select([
                'id',
                'username',
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

        $lastmod = Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->whereIn('author_id', $ids)
            ->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function authorLastmod(User $user): ?CarbonInterface
    {
        $lastmod = Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->where('author_id', $user->id)
            ->max('updated_at');

        return $lastmod
            ? Carbon::parse($lastmod)
            : $user->updated_at;
    }

    private function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/author-sitemap.xml')
            : url("/author-sitemap{$page}.xml");
    }

    private function authorUrl(User $user): string
    {
        return route('author', $user);
    }
}
