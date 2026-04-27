<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\StableSitemapPaginator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostSitemapProvider implements SitemapProviderInterface
{
    public function __construct(
        private readonly StableSitemapPaginator $paginator,
    )
    {
    }

    public function key(): string
    {
        return 'post';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.post.enabled', true);
    }

    public function supports(string $type): bool
    {
        return $type === $this->key();
    }

    public function totalUrls(): int
    {
        return $this->baseIndexableQuery()->count() + $this->homeUrlCount();
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
        $urls = [];

        if ($page === 1 && $this->shouldIncludeHomeInFirstPage()) {
            $urls[] = new SitemapUrlDTO(
                url: url('/'),
                lastmod: $this->homeLastmod(),
                images: [],
            );
        }

        $posts = $this->sitemapContentQuery()
            ->whereIn('id', $this->idsForPage($page))
            ->orderBy('id')
            ->get();

        foreach ($posts as $post) {
            $urls[] = new SitemapUrlDTO(
                url: $this->postUrl($post),
                lastmod: $this->postLastmod($post),
                images: $this->postImages($post),
            );
        }

        return $urls;
    }

    private function allChunkableQuery(): Builder
    {
        return Post::query()
            ->withTrashed();
    }

    private function baseIndexableQuery(): Builder
    {
        return Post::query()
            ->where('status', PostStatusEnum::ACTIVE);
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
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
     * @return array<int, int>
     */
    private function availablePages(): array
    {
        $pages = $this->paginator->publicPages(
            allItemsQuery: $this->allChunkableQuery(),
            indexableQuery: $this->baseIndexableQuery(),
            chunkSize: $this->chunkSize(),
        );

        if ($this->shouldIncludeHomeInFirstPage() && !in_array(1, $pages, true)) {
            array_unshift($pages, 1);
        }

        sort($pages);

        return array_values(array_unique($pages));
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
            return $page === 1 ? $this->homeLastmod() : null;
        }

        $lastmod = $this->baseIndexableQuery()
            ->whereIn('id', $ids)
            ->max('updated_at');

        if (!$lastmod && $page === 1) {
            return $this->homeLastmod();
        }

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    private function homeUrlCount(): int
    {
        return $this->shouldIncludeHomeInFirstPage() ? 1 : 0;
    }

    private function shouldIncludeHomeInFirstPage(): bool
    {
        return (bool)config('seo-sitemap.types.post.include_home_in_first_page', true);
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/post-sitemap.xml')
            : url("/post-sitemap{$page}.xml");
    }

    private function postUrl(Post $post): string
    {
        return $post->url;
    }

    private function postLastmod(Post $post): ?CarbonInterface
    {
        return $post->updated_at ?: $post->published_at;
    }

    private function homeLastmod(): ?CarbonInterface
    {
        $lastmod = $this->baseIndexableQuery()->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function postImages(Post $post): array
    {
        if (!config('seo-sitemap.types.post.include_images', true)) {
            return [];
        }

        return collect()
            ->merge($this->mainImage($post))
            ->merge($this->galleryImages($post))
            ->merge($this->contentImages($post))
            ->filter()
            ->unique(fn(SitemapImageDTO $image) => $image->url)
            ->values()
            ->take(20)
            ->all();
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function mainImage(Post $post): array
    {
        if (!$post->picture?->url) {
            return [];
        }

        return [
            new SitemapImageDTO(
                url: $this->absoluteUrl($post->picture->url),
            ),
        ];
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function galleryImages(Post $post): array
    {
        return $post->imageGallery
            ->filter(fn($imageGallery) => filled($imageGallery->mediaFile?->url))
            ->map(fn($imageGallery) => new SitemapImageDTO(
                url: $this->absoluteUrl($imageGallery->mediaFile->url),
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function contentImages(Post $post): array
    {
        $content = $post->body ?? null;

        if (blank($content)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);

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
